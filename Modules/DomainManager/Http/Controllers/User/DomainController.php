<?php

namespace Modules\DomainManager\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\DomainManager\Entities\DomainRecord;
use Modules\DomainManager\Entities\DomainLog;
use Modules\DomainManager\Http\Requests\AddChildHostRequest;
use Modules\DomainManager\Http\Requests\AddDnsSecRequest;
use Modules\DomainManager\Http\Requests\DeleteChildHostRequest;
use Modules\DomainManager\Http\Requests\ModifyDomainContactsRequest;
use Modules\DomainManager\Http\Requests\RenewDomainRequest;
use Modules\DomainManager\Http\Requests\SyncDomainRequest;
use Modules\DomainManager\Http\Requests\UpdateDomainClientRequest;
use Modules\DomainManager\Http\Requests\UpdateNameserversRequest;
use Modules\DomainManager\Services\ResellerWorldClient;
use Modules\Services\App\Http\Models\Order;
use Throwable;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $resellers = DomainReseller::where('is_active', true)->get();
        $query = DomainRecord::with(['reseller', 'client']);

        // Search
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where('domain_name', 'like', "%{$q}%");
        }

        // Filter by Reseller
        if ($request->filled('reseller_id')) {
            $query->where('domain_reseller_id', $request->input('reseller_id'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting logic
        $sortBy = $request->input('sort_by');
        $sortOrder = strtolower($request->input('sort_order')) === 'asc' ? 'asc' : 'desc';

        $sortableFields = [
            'domain' => 'domain_name',
            'status' => 'status',
            'created' => 'creation_date',
            'expiry' => 'expiry_date',
            'synced' => 'synced_at',
            'id' => 'id',
        ];

        if ($sortBy === 'client') {
            $query->leftJoin('clients', 'domain_records.client_id', '=', 'clients.id')
                ->select('domain_records.*')
                ->orderBy('clients.full_name', $sortOrder);
        } elseif ($sortBy && isset($sortableFields[$sortBy])) {
            $query->orderBy($sortableFields[$sortBy], $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $domains = $query->paginate(15);

        return view('domainmanager::user.domains.index', compact('domains', 'resellers'));
    }

    public function show($domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->with(['client', 'reseller'])->firstOrFail();
        $reseller = $domain->reseller;

        // Check if domain was manually registered in CRM
        $isManual = empty($domain->order_id)
            || str_starts_with((string)$domain->order_id, 'MANUAL-')
            || (isset($domain->raw_details['type']) && $domain->raw_details['type'] === 'manual');

        // Try getting live details only if connected to external API
        $liveDetails = null;
        $error = null;
        if (!$isManual && $reseller) {
            try {
                $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
                $liveDetails = $client->getDomainDetails($domain->domain_name);

                if (isset($liveDetails['status']) && $liveDetails['status'] === 'error') {
                    $error = $liveDetails['message'] ?? 'خطا در دریافت اطلاعات زنده دامنه';
                    $liveDetails = null;
                } else {
                    $ns = $liveDetails['dns'] ?? [];

                    $status = 'active';
                    $expiryDate = $liveDetails['expDate'] ?? null;
                    if ($expiryDate && strtotime($expiryDate) < time()) {
                        $status = 'expired';
                    }

                    $creationDate = $liveDetails['regDate'] ?? null;
                    $privacyProtected = false;

                    $domain->update([
                        'status' => $status,
                        'expiry_date' => $expiryDate,
                        'creation_date' => $creationDate,
                        'nameservers' => $ns,
                        'privacy_protected' => $privacyProtected,
                        'raw_details' => $liveDetails,
                        'synced_at' => now()
                    ]);
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $config = $liveDetails ?: ($domain->raw_details ?? []);
        $childHosts = [];
        if (!$isManual && $reseller) {
            try {
                $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
                $childHostsResult = $client->getChildHosts($domain->domain_name);
                if (is_array($childHostsResult)) {
                    $rawData = isset($childHostsResult['data']) && is_array($childHostsResult['data']) ? $childHostsResult['data'] : $childHostsResult;

                    if (isset($rawData[0]) && is_array($rawData[0])) {
                        foreach ($rawData as $item) {
                            $nsVal = $item['ns'] ?? $item['nameserver'] ?? $item['host'] ?? null;
                            if ($nsVal) {
                                $ipsVal = $item['ips'] ?? $item['ip'] ?? [];
                                $ipsStr = is_array($ipsVal) ? implode(', ', $ipsVal) : $ipsVal;
                                $childHosts[] = [
                                    'ns' => $nsVal,
                                    'ips' => $ipsStr
                                ];
                            }
                        }
                    } else {
                        foreach ($rawData as $nsVal => $ipsVal) {
                            if (in_array($nsVal, ['status', 'message', 'success'])) {
                                continue;
                            }
                            $ipsStr = is_array($ipsVal) ? implode(', ', $ipsVal) : $ipsVal;
                            $childHosts[] = [
                                'ns' => $nsVal,
                                'ips' => $ipsStr
                            ];
                        }
                    }
                }
            } catch (Throwable $e) {}
        }

        $domain->load(['serviceOrder.service', 'serviceOrder.customer']);

        $allOrdersList = [];
        if (class_exists(Order::class)) {
            $allOrdersList = Order::whereHas('service.category', function ($q) {
                $q->where('slug', 'domain');
            })
            ->with(['customer', 'service.category'])
            ->latest()
            ->get();
        }

        return view('domainmanager::user.domains.show', compact('domain', 'config', 'error', 'childHosts', 'allOrdersList'));
    }

    /**
     * Sync Domain cache manually
     */
    public function sync(SyncDomainRequest $request)
    {
        $domain = DomainRecord::findOrFail($request->input('domain_id'));
        $reseller = $domain->reseller;

        $isManual = empty($domain->order_id)
            || str_starts_with((string)$domain->order_id, 'MANUAL-')
            || (isset($domain->raw_details['type']) && $domain->raw_details['type'] === 'manual');

        if ($isManual) {
            return back()->with('info', "دامنه {$domain->domain_name} به صورت دستی در سامانه ثبت شده است و بدون اتصال به وب‌سرویس کارگزار مدیریت می‌شود.");
        }

        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $liveDetails = $client->getDomainDetails($domain->domain_name);

            if (isset($liveDetails['status']) && $liveDetails['status'] === 'error') {
                return back()->with('error', 'خطا در همگام‌سازی از کارگزار: ' . ($liveDetails['message'] ?? ''));
            }

            $ns = $liveDetails['dns'] ?? [];

            $status = 'active';
            $expiryDate = $liveDetails['expDate'] ?? null;
            if ($expiryDate && strtotime($expiryDate) < time()) {
                $status = 'expired';
            }

            $creationDate = $liveDetails['regDate'] ?? null;
            $privacyProtected = false;

            $domain->update([
                'status' => $status,
                'expiry_date' => $expiryDate,
                'creation_date' => $creationDate,
                'nameservers' => $ns,
                'privacy_protected' => $privacyProtected,
                'raw_details' => $liveDetails,
                'synced_at' => now()
            ]);

            DomainLog::log('sync', "همگام‌سازی اطلاعات دامنه {$domain->domain_name} با موفقیت انجام شد.", $domain->domain_name, $reseller->id);

            return back()->with('status', "اطلاعات دامنه {$domain->domain_name} با موفقیت همگام‌سازی شد.");
        } catch (Throwable $e) {
            return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
        }
    }

    /**
     * Sync all domains from the reseller account
     */
    public function syncAll(Request $request)
    {
        $resellerId = $request->input('reseller_id');
        $reseller = null;

        if ($resellerId) {
            $reseller = DomainReseller::find($resellerId);
        } else {
            $reseller = DomainReseller::where('is_active', true)->first();
        }

        if (!$reseller) {
            return back()->with('error', 'هیچ کارگزار فعالی برای همگام‌سازی یافت نشد.');
        }

        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);

            // Sync wallet balance (not supported by API, keeping local cache)
            $balanceInfo = $client->getBalance();
            if (isset($balanceInfo['sellingbalance'])) {
                $reseller->update(['balance' => (float) $balanceInfo['sellingbalance']]);
            }

            $syncedCount = 0;
            $newCount = 0;
            $page = 1;

            do {
                $searchResult = $client->searchDomains($page, 20);

                if (isset($searchResult['status']) && $searchResult['status'] === 'error') {
                    return back()->with('error', 'خطا در ارتباط با کارگزار: ' . ($searchResult['message'] ?? ''));
                }

                if (!is_array($searchResult) || !isset($searchResult['data']) || empty($searchResult['data'])) {
                    break;
                }

                foreach ($searchResult['data'] as $orderData) {
                    $domainName = $orderData['domain'] ?? null;
                    if (empty($domainName)) {
                        continue;
                    }

                    $orderId = $orderData['id'] ?? null;
                    $expiryDate = $orderData['expire_at'] ?? null;
                    $creationDate = $orderData['created_at'] ?? null;

                    // Set status
                    $status = 'active';
                    if ($expiryDate && strtotime($expiryDate) < time()) {
                        $status = 'expired';
                    }

                    // Find or create local domain record
                    $domainRecord = DomainRecord::where('domain_reseller_id', $reseller->id)
                        ->where('domain_name', $domainName)
                        ->first();

                    if ($domainRecord) {
                        $domainRecord->update([
                            'order_id' => $orderId,
                            'status' => $status,
                            'expiry_date' => $expiryDate,
                            'creation_date' => $creationDate,
                            'synced_at' => now()
                        ]);
                        $syncedCount++;
                    } else {
                        DomainRecord::create([
                            'domain_reseller_id' => $reseller->id,
                            'order_id' => $orderId,
                            'domain_name' => $domainName,
                            'status' => $status,
                            'expiry_date' => $expiryDate,
                            'creation_date' => $creationDate,
                            'synced_at' => now()
                        ]);
                        $newCount++;
                    }
                }

                // If less than 20 records returned, we reached the end
                if (count($searchResult['data']) < 20) {
                    break;
                }

                $page++;
            } while ($page <= 25);

            DomainLog::log('sync_all', "همگام‌سازی دسته‌جمعی دامنه‌ها انجام شد. بروزرسانی کش: {$syncedCount} دامنه، دامنه‌های جدید: {$newCount} دامنه.", null, $reseller->id);

            return back()->with('status', "همگام‌سازی با موفقیت انجام شد. بروزرسانی کش: {$syncedCount} دامنه، دامنه‌های جدید اضافه شده: {$newCount} دامنه.");

        } catch (Throwable $e) {
            return back()->with('error', 'خطای سیستمی در همگام‌سازی: ' . $e->getMessage());
        }
    }

    /**
     * Modify Domain Nameservers
     */
    public function modifyNS(UpdateNameserversRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $ns = array_filter([
            $request->input('ns1'),
            $request->input('ns2'),
            $request->input('ns3'),
            $request->input('ns4')
        ]);

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->modifyNameservers($domain->domain_name, $ns);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('modify_ns', "خطا در تغییر کارگزارهای نام دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در ویرایش دی‌ان‌اس‌ها: ' . ($result['message'] ?? ''));
        }

        $domain->update([
            'nameservers' => $ns,
            'synced_at' => now()
        ]);

        DomainLog::log('modify_ns', "کارگزارهای نام دامنه {$domainName} با موفقیت به [" . implode(', ', $ns) . "] تغییر یافتند.", $domainName, $reseller->id);

        return back()->with('status', 'دی‌ان‌اس‌های دامنه با موفقیت در کارگزاری بروزرسانی شدند.');
    }

    /**
     * Toggle WHOIS privacy protection
     */
    public function togglePrivacy(Request $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $protect = $request->boolean('privacy');

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->modifyPrivacyProtection($domain->domain_name, $protect);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('privacy_protection', "خطا در تغییر وضعیت حریم خصوصی دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در تغییر وضعیت مخفی‌سازی WHOIS: ' . ($result['message'] ?? ''));
        }

        $domain->update([
            'privacy_protected' => $protect,
            'synced_at' => now()
        ]);

        $statusStr = $protect ? 'فعال' : 'غیرفعال';
        DomainLog::log('privacy_protection', "مخفی‌سازی مشخصات WHOIS دامنه {$domainName} با موفقیت {$statusStr} گردید.", $domainName, $reseller->id);

        return back()->with('status', "مخفی‌سازی مشخصات WHOIS با موفقیت {$statusStr} شد.");
    }

    /**
     * Lock/Unlock Domain
     */
    public function toggleLock(Request $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $lock = $request->boolean('lock');

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->toggleLock($domain->domain_name, $lock);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('toggle_lock', "خطا در تغییر قفل دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در تغییر وضعیت قفل دامنه: ' . ($result['message'] ?? ''));
        }

        $statusStr = $lock ? 'فعال' : 'غیرفعال';
        DomainLog::log('toggle_lock', "قفل امنیتی انتقال دامنه {$domainName} با موفقیت {$statusStr} شد.", $domainName, $reseller->id);

        return back()->with('status', "قفل دامنه با موفقیت {$statusStr} گردید.");
    }

    /**
     * Renew Domain
     */
    public function renew(RenewDomainRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $years = (int) $request->input('years');

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->renewDomain($domain->domain_name, $years);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('renew', "خطا در درخواست تمدید {$years} ساله دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در تمدید دامنه: ' . ($result['message'] ?? ''));
        }

        // Add years to local expiry cache
        $newExpiry = $domain->expiry_date ? $domain->expiry_date->addYears($years) : null;
        $domain->update([
            'expiry_date' => $newExpiry,
            'status' => 'active',
            'synced_at' => now()
        ]);

        DomainLog::log('renew', "درخواست تمدید {$years} ساله دامنه {$domainName} با موفقیت ارسال شد و انجام گردید.", $domainName, $reseller->id);

        return back()->with('status', "دامنه {$domainName} با موفقیت به مدت {$years} سال تمدید شد.");
    }

    /**
     * Modify Domain Contacts
     */
    public function modifyContacts(ModifyDomainContactsRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $data = $request->validated();

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->modifyContacts($domain->domain_name, $data);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('modify_contacts', "خطا در تغییر مشخصات WHOIS دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در بروزرسانی اطلاعات تماس: ' . ($result['message'] ?? ''));
        }

        DomainLog::log('modify_contacts', "اطلاعات تماس مالک (WHOIS) دامنه {$domainName} با موفقیت بروزرسانی شد.", $domainName, $reseller->id);

        return back()->with('status', 'مشخصات تماس مالک دامنه با موفقیت تغییر یافت.');
    }

    /**
     * TLD Prices list view
     */
    public function prices()
    {
        $reseller = DomainReseller::where('is_active', true)->first();
        if (!$reseller) {
            return redirect()->route('user.domainmanager.connections.index')->with('info', 'ابتدا یک کارگزار فعال کنید.');
        }

        $prices = [];
        $error = null;
        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $rawPrices = $client->getPrices();

            Log::info("=== [DOMAIN PRICES DIAGNOSTIC LOG START] ===");
            Log::info("Raw API Response Type: " . gettype($rawPrices));
            Log::info("Raw API Keys: " . json_encode(is_array($rawPrices) ? array_keys($rawPrices) : []));

            if (isset($rawPrices['status']) && $rawPrices['status'] === 'error') {
                $rawMsg = $rawPrices['message'] ?? '';
                $error = 'امکان دریافت زنده جدول قیمت‌ها در حال حاضر از کارگزار وجود ندارد (' . ($rawMsg ?: 'خطای عدم پاسخگویی کارگزار') . ')';
                $prices = [];
                Log::error("[DomainController] API Price Fetch Error: " . json_encode($rawPrices));
            } else {
                // OpenAPI spec returns object where top-level keys are TLD names ('com', 'net', 'org', etc.)
                $prices = is_array($rawPrices) ? $rawPrices : [];
                unset($prices['status'], $prices['message']);

                Log::info("Processed Prices TLD Count: " . count($prices));
            }
            Log::info("=== [DOMAIN PRICES DIAGNOSTIC LOG END] ===");
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('domainmanager::user.prices.index', compact('prices', 'error'));
    }

    /**
     * Activity Logs list view
     */
    public function logs(Request $request)
    {
        $resellers = DomainReseller::where('is_active', true)->get();
        $query = DomainLog::with(['reseller', 'user']);

        // Filter by Search term
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('domain_name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // Filter by Action
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter by Reseller
        if ($request->filled('reseller_id')) {
            $query->where('domain_reseller_id', $request->input('reseller_id'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->orderBy('id', 'desc')->paginate(15);

        // Stats
        $totalLogsCount = DomainLog::count();
        $successLogsCount = DomainLog::where('status', 'success')->count();
        $failedLogsCount = DomainLog::where('status', 'failed')->count();
        $syncLogsCount = DomainLog::whereIn('action', ['sync', 'sync_all'])->count();

        return view('domainmanager::user.logs.index', compact(
            'logs',
            'resellers',
            'totalLogsCount',
            'successLogsCount',
            'failedLogsCount',
            'syncLogsCount'
        ));
    }

    /**
     * Reseller API Transactions list view
     */
    public function transactions(Request $request)
    {
        $reseller = DomainReseller::where('is_active', true)->first();
        if (!$reseller) {
            return redirect()->route('user.domainmanager.connections.index')->with('info', 'ابتدا یک کارگزار فعال کنید.');
        }

        $transactions = [];
        $error = null;
        $page = $request->input('page', 1);

        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $transactions = $client->getTransactions($page);
            if (isset($transactions['status']) && $transactions['status'] === 'error') {
                $error = $transactions['message'] ?? 'خطا در دریافت تراکنش‌ها';
                $transactions = [];
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('domainmanager::user.transactions.index', compact('transactions', 'error', 'page'));
    }

    /**
     * Add child host NS
     */
    public function addChildHost(AddChildHostRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->addChildHost($domain->domain_name, $request->input('ns'), $request->input('ip'));

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('child_ns', "خطا در ثبت دی‌ان‌اس کارگزار فرزند {$request->input('ns')} برای دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در ثبت کارگزار فرزند: ' . ($result['message'] ?? ''));
        }

        DomainLog::log('child_ns', "ثبت کارگزار فرزند جدید {$request->input('ns')} با IP {$request->input('ip')} برای دامنه {$domainName}.", $domainName, $reseller->id);

        return back()->with('status', 'کارگزار فرزند با موفقیت ایجاد شد.');
    }

    /**
     * Delete child host NS
     */
    public function deleteChildHost(DeleteChildHostRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->deleteChildHost($domain->domain_name, $request->input('ns'));

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('child_ns', "خطا در حذف کارگزار فرزند {$request->input('ns')} برای دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در حذف کارگزار فرزند: ' . ($result['message'] ?? ''));
        }

        DomainLog::log('child_ns', "حذف کارگزار فرزند {$request->input('ns')} برای دامنه {$domainName}.", $domainName, $reseller->id);

        return back()->with('status', 'کارگزار فرزند با موفقیت حذف شد.');
    }

    /**
     * Add DNSSEC key
     */
    public function addDnsSec(AddDnsSecRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->addDnsSec(
            $domain->domain_name,
            $request->input('tag'),
            $request->input('algorithm'),
            $request->input('digestType'),
            $request->input('digest')
        );

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('dnssec', "خطا در ثبت کلید DNSSEC برای دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در فعال‌سازی DNSSEC: ' . ($result['message'] ?? ''));
        }

        DomainLog::log('dnssec', "فعال‌سازی و ثبت کلید DNSSEC جدید برای دامنه {$domainName}.", $domainName, $reseller->id);

        return back()->with('status', 'کلید DNSSEC با موفقیت اضافه شد.');
    }

    /**
     * Delete DNSSEC key
     */
    public function deleteDnsSec(Request $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->deleteDnsSec($domain->domain_name);

        if (isset($result['status']) && $result['status'] === 'error') {
            DomainLog::log('dnssec', "خطا در حذف کلید DNSSEC برای دامنه {$domainName}: " . ($result['message'] ?? ''), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در حذف DNSSEC: ' . ($result['message'] ?? ''));
        }

        DomainLog::log('dnssec', "حذف کلیدهای DNSSEC برای دامنه {$domainName}.", $domainName, $reseller->id);

        return back()->with('status', 'رکورد DNSSEC با موفقیت غیرفعال و حذف شد.');
    }

    /**
     * Explicitly fetch/request EPP Auth Code from registrar upon user request
     */
    public function fetchEpp($domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $reseller = $domain->reseller;

        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $eppResult = $client->getAuthCode($domain->domain_name);

            if (isset($eppResult['status']) && $eppResult['status'] === 'error') {
                DomainLog::log('epp', "خطا در استعلام کد EPP دامنه {$domainName}: " . ($eppResult['message'] ?? ''), $domainName, $reseller->id, 'failed');
                return back()->with('error', 'خطا در استعلام کد EPP از کارگزار: ' . ($eppResult['message'] ?? ''));
            }

            $authCode = is_string($eppResult) ? $eppResult : ($eppResult['epp'] ?? ($eppResult['authCode'] ?? null));

            if ($authCode) {
                $domain->update(['auth_code' => $authCode]);
            }

            DomainLog::log('epp', "درخواست دریافت کد EPP برای دامنه {$domainName} ثبت شد.", $domainName, $reseller->id);
            return back()->with('status', 'درخواست کد EPP با موفقیت به کارگزار ارسال شد و ایمیل حاوی کد برای مالک دامنه ارسال گردید.');
        } catch (Throwable $e) {
            DomainLog::log('epp', "استثنا در دریافت کد EPP دامنه {$domainName}: " . $e->getMessage(), $domainName, $reseller->id, 'failed');
            return back()->with('error', 'خطا در ارتباط با کارگزار جهت دریافت کد EPP: ' . $e->getMessage());
        }
    }

    /**
     * Update client associated with domain
     */
    public function updateClient(UpdateDomainClientRequest $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();

        if (!Schema::hasColumn('domain_records', 'client_id')) {
            return back()->with('error', 'ستون client_id هنوز روی دیتابیس اجرا نشده است. لطفاً ابتدا میگریشن دیتابیس را اجرا کنید.');
        }

        $domain->update([
            'client_id' => $request->input('client_id'),
        ]);

        DomainLog::log('update', "تغییر مالک/مشتری متصل به دامنه {$domainName}.", $domainName, $domain->domain_reseller_id);

        return back()->with('status', 'مشتری متصل به دامنه با موفقیت بروزرسانی شد.');
    }

    /**
     * Update or unlink the CRM service order associated with this domain
     */
    public function updateOrder(Request $request, $domainName)
    {
        $domain = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $orderId = $request->input('service_order_id');

        if ($orderId) {
            $order = Order::findOrFail($orderId);
            $domain->update([
                'service_order_id' => $order->id,
                'client_id' => $domain->client_id ?: $order->customer_id,
            ]);
            DomainLog::log('update', "اتصال دامنه {$domainName} به سفارش #{$order->order_number}.", $domainName, $domain->domain_reseller_id);
            return back()->with('status', "دامنه با موفقیت به سفارش شماره {$order->order_number} متصل گردید.");
        } else {
            $prevOrderId = $domain->service_order_id;
            $domain->update(['service_order_id' => null]);
            DomainLog::log('update', "حذف اتصال دامنه {$domainName} از سفارش #{$prevOrderId}.", $domainName, $domain->domain_reseller_id);
            return back()->with('status', 'اتصال دامنه از سفارش با موفقیت حذف گردید.');
        }
    }

    /**
     * Store and register a new domain from the Order page and link it directly to service_order_id
     */
    public function storeOrderDomain(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'reseller_id' => 'required|exists:domain_resellers,id',
            'domain_name' => 'required|string|max:255',
            'years' => 'required|integer|min:1|max:10',
            'action_type' => 'required|in:register,transfer',
            'auth_code' => 'nullable|required_if:action_type,transfer|string',
            'holder_handle' => 'nullable|string',
            'ns1' => 'nullable|string',
            'ns2' => 'nullable|string',
            'ns3' => 'nullable|string',
            'ns4' => 'nullable|string',
        ]);

        $orderId = (int)$request->input('order_id');
        $domainName = strtolower(trim($request->input('domain_name')));
        $domainName = preg_replace('/^https?:\/\//i', '', $domainName);
        $domainName = preg_replace('/^www\./i', '', $domainName);

        $reseller = DomainReseller::findOrFail($request->input('reseller_id'));
        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);

        $years = (int)$request->input('years', 1);
        $actionType = $request->input('action_type', 'register');
        $holderHandle = trim((string)$request->input('holder_handle', ''));

        // If international domain and custom contact fields are submitted
        if (!str_ends_with($domainName, '.ir') && $request->filled('contact_email') && $request->filled('contact_name')) {
            try {
                $domainParts = explode('.', $domainName);
                $tld = end($domainParts);
                $contactResult = $client->createContact(
                    $tld,
                    $request->input('contact_email'),
                    $request->input('contact_name'),
                    $request->input('contact_country', 'IR'),
                    $request->input('contact_state', 'Tehran'),
                    $request->input('contact_city', 'Tehran'),
                    $request->input('contact_address', 'Main Street'),
                    $request->input('contact_postcode', '1234567890'),
                    $request->input('contact_phone', '+98.9120000000'),
                    $request->input('contact_org', 'Personal')
                );

                if (isset($contactResult['contact_id'])) {
                    $holderHandle = $contactResult['contact_id'];
                } elseif (isset($contactResult['id'])) {
                    $holderHandle = $contactResult['id'];
                }
            } catch (Throwable $e) {
                // If contact creation fails, continue with default or provided handle
            }
        }

        $ns = array_values(array_filter([
            $request->input('ns1'),
            $request->input('ns2'),
            $request->input('ns3'),
            $request->input('ns4'),
        ]));

        if (empty($ns)) {
            $ns = ['ns1.reseller.world', 'ns2.reseller.world'];
        }

        try {
            if ($actionType === 'transfer') {
                $authCode = $request->input('auth_code');
                $apiResult = $client->transferDomain($domainName, $authCode, $holderHandle, $ns);
            } else {
                $apiResult = $client->registerDomain($domainName, $years, $holderHandle, $ns);
            }

            if (isset($apiResult['status']) && $apiResult['status'] === 'error') {
                $errorMsg = $apiResult['message'] ?? 'خطا در ثبت درخواست دامنه در کارگزاری';
                DomainLog::log($actionType, "خطا در {$actionType} دامنه {$domainName} برای سفارش #{$orderId}: {$errorMsg}", $domainName, $reseller->id, 'failed');
                return response()->json([
                    'success' => false,
                    'message' => 'خطای کارگزاری: ' . $errorMsg,
                ], 422);
            }

            $remoteOrderId = $apiResult['trackId'] ?? $apiResult['entityid'] ?? null;

            // Find customer_id from order if model exists
            $clientId = null;
            if (class_exists(Order::class)) {
                $orderObj = Order::find($orderId);
                $clientId = $orderObj?->customer_id;
            }

            $domainRecord = DomainRecord::updateOrCreate(
                ['domain_name' => $domainName],
                [
                    'domain_reseller_id' => $reseller->id,
                    'client_id' => $clientId,
                    'service_order_id' => $orderId,
                    'order_id' => $remoteOrderId,
                    'status' => $actionType === 'transfer' ? 'pending' : 'active',
                    'expiry_date' => $actionType === 'transfer' ? null : now()->addYears($years),
                    'creation_date' => now(),
                    'nameservers' => $ns,
                    'auth_code' => $request->input('auth_code'),
                    'privacy_protected' => false,
                    'synced_at' => now(),
                ]
            );

            $actionTitle = $actionType === 'transfer' ? 'انتقال' : 'ثبت آنلاین';
            DomainLog::log(
                $actionType,
                "{$actionTitle} دامنه {$domainName} برای سفارش #{$orderId} با موفقیت در کارگزار {$reseller->name} انجام شد. شناسه پیگیری: {$remoteOrderId}",
                $domainName,
                $reseller->id,
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => "درخواست {$actionTitle} دامنه {$domainName} با موفقیت در کارگزاری ثبت و به سفارش متصل گردید.",
                'domain' => $domainRecord,
            ]);

        } catch (Throwable $e) {
            DomainLog::log($actionType, "استثنا در {$actionType} دامنه {$domainName} برای سفارش #{$orderId}: " . $e->getMessage(), $domainName, $reseller->id, 'failed');
            return response()->json([
                'success' => false,
                'message' => 'خطای سیستمی: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Link an existing registered domain to an order
     */
    public function linkOrderDomain(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'domain_name' => 'required|exists:domain_records,domain_name',
        ]);

        $orderId = (int)$request->input('order_id');
        $domainName = strtolower(trim($request->input('domain_name')));

        $clientId = null;
        if (class_exists(Order::class)) {
            $orderObj = Order::find($orderId);
            $clientId = $orderObj?->customer_id;
        }

        $domainRecord = DomainRecord::where('domain_name', $domainName)->firstOrFail();
        $domainRecord->update([
            'service_order_id' => $orderId,
            'client_id' => $clientId ?: $domainRecord->client_id,
        ]);

        DomainLog::log('update', "اتصال دامنه {$domainName} به سفارش #{$orderId}.", $domainName, $domainRecord->domain_reseller_id);

        return response()->json([
            'success' => true,
            'message' => "دامنه {$domainName} با موفقیت به این سفارش متصل گردید.",
            'domain' => $domainRecord,
        ]);
    }

    /**
     * Unlink a domain from an order
     */
    public function unlinkOrderDomain(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'domain_name' => 'required|exists:domain_records,domain_name',
        ]);

        $orderId = (int)$request->input('order_id');
        $domainName = strtolower(trim($request->input('domain_name')));

        DomainRecord::where('domain_name', $domainName)
            ->where('service_order_id', $orderId)
            ->update(['service_order_id' => null]);

        DomainLog::log('update', "حذف اتصال دامنه {$domainName} از سفارش #{$orderId}.", $domainName, null);

        return response()->json([
            'success' => true,
            'message' => "اتصال دامنه {$domainName} با موفقیت از این سفارش حذف شد.",
        ]);
    }

    /**
     * Quick AJAX check availability for Order Modal
     */
    public function checkOrderDomainAvailability(Request $request)
    {
        $request->validate([
            'reseller_id' => 'required|exists:domain_resellers,id',
            'domain' => 'required|string',
        ]);

        $reseller = DomainReseller::findOrFail($request->input('reseller_id'));
        $domainInput = ltrim(trim($request->input('domain')), '.');

        $domainParts = explode('.', $domainInput);
        $domainName = $domainParts[0];
        $tld = count($domainParts) > 1 ? end($domainParts) : 'com';

        if ($tld === 'ir') {
            $hasNs = checkdnsrr("{$domainName}.ir", 'NS');
            $whoisResult = '';
            $fp = @fsockopen('whois.nic.ir', 43, $errno, $errstr, 4);
            if ($fp) {
                fputs($fp, "{$domainName}.ir\r\n");
                while (!feof($fp)) {
                    $whoisResult .= fgets($fp, 128);
                }
                fclose($fp);
            }
            $isFree = str_contains(strtolower($whoisResult), 'no entries found') || (empty($whoisResult) && !$hasNs);
            return response()->json([
                'domain' => "{$domainName}.ir",
                'available' => $isFree,
                'status' => $isFree ? 'available' : 'unavailable',
            ]);
        }

        try {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $check = $client->checkAvailability($domainName, [$tld]);
            $full = "{$domainName}.{$tld}";
            $isAvail = isset($check[$full]['status']) && $check[$full]['status'] === 'available';

            return response()->json([
                'domain' => $full,
                'available' => $isAvail,
                'status' => $isAvail ? 'available' : 'unavailable',
            ]);
        } catch (Throwable $e) {
            $hasNs = checkdnsrr("{$domainName}.{$tld}", 'NS') || checkdnsrr("{$domainName}.{$tld}", 'A');
            return response()->json([
                'domain' => "{$domainName}.{$tld}",
                'available' => !$hasNs,
                'status' => !$hasNs ? 'available' : 'unavailable',
            ]);
        }
    }
}
