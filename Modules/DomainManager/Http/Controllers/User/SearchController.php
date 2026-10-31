<?php

namespace Modules\DomainManager\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\DomainManager\Entities\DomainRecord;
use Modules\DomainManager\Entities\DomainLog;
use Modules\DomainManager\Http\Requests\CheckDomainRequest;
use Modules\DomainManager\Http\Requests\CheckIrDomainRequest;
use Modules\DomainManager\Http\Requests\ShowRegisterFormRequest;
use Modules\DomainManager\Http\Requests\RegisterDomainRequest;
use Modules\DomainManager\Http\Requests\TransferDomainRequest;
use Modules\DomainManager\Services\ResellerWorldClient;

class SearchController extends Controller
{
    public function index()
    {
        $resellers = DomainReseller::where('is_active', true)->get();
        return view('domainmanager::user.search.index', compact('resellers'));
    }

    /**
     * AJAX Check availability
     */
    public function check(CheckDomainRequest $request)
    {
        $reseller = DomainReseller::findOrFail($request->input('reseller_id'));
        $domainInput = ltrim(trim($request->input('domain')), '.');

        // Extract domain name and extension
        $domainParts = explode('.', $domainInput);
        $domainName = $domainParts[0];
        $defaultTlds = count($domainParts) > 1 ? [end($domainParts)] : ['com', 'net', 'org', 'info', 'ir'];

        $tlds = $request->input('tlds', $defaultTlds);

        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
        $result = $client->checkAvailability($domainName, $tlds);

        // Enhance results with IRNIC socket check for .ir domains and DNS fallback for other TLDs
        foreach ($tlds as $tld) {
            $tldClean = ltrim(strtolower($tld), '.');
            $fullDomain = "{$domainName}.{$tldClean}";

            if ($tldClean === 'ir') {
                $irCheck = $this->performIrCheck($fullDomain);
                $result[$fullDomain] = [
                    'status' => $irCheck['status'],
                    'provider' => 'IRNIC Direct WHOIS'
                ];
            } elseif (!isset($result[$fullDomain])) {
                $hasNs = checkdnsrr($fullDomain, 'NS') || checkdnsrr($fullDomain, 'A');
                $result[$fullDomain] = [
                    'status' => $hasNs ? 'unavailable' : 'available',
                    'provider' => 'DNS Fallback'
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * Dedicated free WHOIS check for .IR domains directly via IRNIC (whois.nic.ir)
     */
    public function checkIrDomain(CheckIrDomainRequest $request)
    {
        $domainInput = ltrim(trim($request->input('domain')), '.');
        $domainName = explode('.', $domainInput)[0];
        $targetDomain = $domainName . '.ir';

        $irCheck = $this->performIrCheck($targetDomain);

        return response()->json([
            'domain' => $targetDomain,
            'status' => $irCheck['status'],
            'whois_server' => 'whois.nic.ir',
            'dns_has_ns' => $irCheck['has_dns'],
        ]);
    }

    /**
     * Perform direct IRNIC WHOIS socket check
     */
    private function performIrCheck($fullDomain)
    {
        $hasNs = checkdnsrr($fullDomain, 'NS');
        $whoisResult = '';
        $fp = @fsockopen('whois.nic.ir', 43, $errno, $errstr, 4);
        if ($fp) {
            fputs($fp, $fullDomain . "\r\n");
            while (!feof($fp)) {
                $whoisResult .= fgets($fp, 128);
            }
            fclose($fp);
        }

        $isFree = str_contains(strtolower($whoisResult), 'no entries found') || (empty($whoisResult) && !$hasNs);
        return [
            'status' => $isFree ? 'available' : 'unavailable',
            'has_dns' => $hasNs,
            'raw' => substr($whoisResult, 0, 300)
        ];
    }

    public function showRegisterForm(ShowRegisterFormRequest $request)
    {
        $resellers = DomainReseller::where('is_active', true)->get();

        if ($resellers->isEmpty()) {
            $anyReseller = DomainReseller::first();
            if (!$anyReseller) {
                return redirect()->route('user.domainmanager.connections.index')
                    ->with('info', 'لطفاً ابتدا یک حساب کارگزاری دامنه ثبت و فعال کنید.');
            }
            $resellers = DomainReseller::all();
        }

        $domain = $request->input('domain');
        $resellerId = $request->input('reseller_id');
        $action = $request->input('action', 'register') ?: 'register';
        $registrationMode = $request->input('mode', 'manual') ?: 'manual';

        $reseller = $resellerId ? $resellers->firstWhere('id', $resellerId) : null;
        if (!$reseller) {
            $reseller = $resellers->first();
        }

        return view('domainmanager::user.search.register', compact('domain', 'reseller', 'resellers', 'action', 'registrationMode'));
    }

    /**
     * Register Domain action
     */
    public function register(RegisterDomainRequest $request)
    {
        $data = $request->validated();
        $domainName = strtolower(trim($data['domain']));

        // Check if domain is already recorded in the system
        $existing = DomainRecord::where('domain_name', $domainName)->first();
        if ($existing) {
            return back()->withInput()->with('error', "دامنه {$domainName} قبلاً در سامانه ثبت شده است (شناسه رکورد #{$existing->id}).");
        }

        $reseller = DomainReseller::findOrFail($data['reseller_id']);
        $ns = array_values(array_filter([$data['ns1'], $data['ns2'], $data['ns3'] ?? null, $data['ns4'] ?? null]));
        $isManual = ($data['registration_mode'] ?? 'manual') === 'manual'
            || $reseller->auth_userid === 'manual'
            || empty($reseller->api_key);

        if (!$isManual) {
            $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);
            $holderHandle = !empty($data['holder_handle']) ? $data['holder_handle'] : $reseller->auth_userid;
            $regResult = $client->registerDomain($domainName, $data['years'], $holderHandle, $ns);

            if (isset($regResult['status']) && $regResult['status'] === 'error') {
                DomainLog::log('register', "خطا در ثبت آنلاین دامنه {$domainName} از طریق کارگزار: " . ($regResult['message'] ?? ''), $domainName, $reseller->id, 'failed');
                return back()->withInput()->with('error', 'خطا در ثبت دامنه: ' . ($regResult['message'] ?? ''));
            }

            $orderId = $regResult['trackId'] ?? $regResult['entityid'] ?? ('RW-' . time());
            $successMsg = "درخواست ثبت آنلاین دامنه {$domainName} با موفقیت به کارگزار ارسال شد. شناسه پیگیری: {$orderId}";

            DomainLog::log('register', "ثبت آنلاین دامنه {$domainName} با موفقیت در کارگزار انجام شد (پیگیری: {$orderId}).", $domainName, $reseller->id, 'success');
        } else {
            // Manual Registration in CRM (No upstream API call)
            $orderId = 'MANUAL-' . strtoupper(Str::random(8));
            $successMsg = "دامنه {$domainName} با موفقیت به صورت دستی در سامانه ثبت و فعال گردید.";

            DomainLog::log('register', "دامنه {$domainName} به صورت دستی در سامانه ثبت گردید (شناسه دستی: {$orderId}).", $domainName, $reseller->id, 'success');
        }

        DomainRecord::create([
            'domain_reseller_id' => $reseller->id,
            'order_id' => $orderId,
            'domain_name' => $domainName,
            'status' => 'active',
            'expiry_date' => now()->addYears((int) $data['years']),
            'creation_date' => now(),
            'nameservers' => $ns,
            'privacy_protected' => false,
            'raw_details' => [
                'type' => $isManual ? 'manual' : 'api',
                'registered_by_user_id' => auth()->id(),
                'registered_at' => now()->toIso8601String(),
                'holder_handle' => $data['holder_handle'] ?? null,
                'note' => $isManual ? 'ثبت دستی در سامانه CRM بدون تماس با وب‌سرویس کارگزار' : 'ثبت آنلاین از طریق وب‌سرویس کارگزار'
            ],
            'synced_at' => now()
        ]);

        return redirect()->route('user.domainmanager.domains.index')
            ->with('status', $successMsg);
    }

    /**
     * Transfer Domain action
     */
    public function transfer(TransferDomainRequest $request)
    {
        $data = $request->validated();

        $reseller = DomainReseller::findOrFail($data['reseller_id']);
        $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);

        $ns = array_filter([$data['ns1'], $data['ns2'], $data['ns3'] ?? null, $data['ns4'] ?? null]);
        $transResult = $client->transferDomain($data['domain'], $data['auth_code'], $data['holder_handle'], $ns);

        if (isset($transResult['status']) && $transResult['status'] === 'error') {
            return back()->withInput()->with('error', 'خطا در فرآیند انتقال دامنه: ' . ($transResult['message'] ?? ''));
        }

        $orderId = $transResult['trackId'] ?? $transResult['entityid'] ?? null;

        DomainRecord::create([
            'domain_reseller_id' => $reseller->id,
            'order_id' => $orderId,
            'domain_name' => $data['domain'],
            'status' => 'pending',
            'expiry_date' => null,
            'creation_date' => null,
            'nameservers' => $ns,
            'privacy_protected' => false,
            'auth_code' => $data['auth_code'],
            'synced_at' => now()
        ]);

        return redirect()->route('user.domainmanager.domains.index')
            ->with('status', "درخواست انتقال دامنه {$data['domain']} ارسال شد. شناسه پیگیری: {$orderId}");
    }
}
