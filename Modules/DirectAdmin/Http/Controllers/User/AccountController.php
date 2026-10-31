<?php

namespace Modules\DirectAdmin\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Entities\Client;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaAccount;
use Modules\DirectAdmin\Entities\DaSetting;
use Modules\DirectAdmin\Http\Requests\StoreAccountRequest;
use Modules\DirectAdmin\Http\Requests\SyncAccountsRequest;
use Modules\DirectAdmin\Http\Requests\UpdateAccountClientRequest;
use Modules\DirectAdmin\Services\DirectAdminClient;
use Carbon\Carbon;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Status;
use Throwable;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $servers = DaServer::where('is_active', true)->get();

        $query = DaAccount::with(['server', 'client', 'order.service']);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('domain', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('server_id')) {
            $query->where('da_server_id', $request->input('server_id'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'suspended') {
                $query->where('suspended', true);
            } elseif ($status === 'active') {
                $query->where('suspended', false);
            }
        }

        if ($request->filled('package')) {
            $query->where('package', $request->input('package'));
        }

        // Sorting logic
        $sortBy = $request->input('sort_by');
        $sortOrder = strtolower($request->input('sort_order')) === 'asc' ? 'asc' : 'desc';

        $sortableFields = [
            'user' => 'username',
            'package' => 'package',
            'disk' => 'disk_usage_bytes',
            'status' => 'suspended',
            'synced' => 'synced_at',
            'id' => 'id',
        ];

        if ($sortBy === 'client') {
            $query->leftJoin('clients', 'da_accounts.client_id', '=', 'clients.id')
                ->select('da_accounts.*')
                ->orderBy('clients.full_name', $sortOrder);
        } elseif ($sortBy && isset($sortableFields[$sortBy])) {
            $query->orderBy($sortableFields[$sortBy], $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = (int)$request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $accounts = $query->paginate($perPage);
        $packages = DaAccount::distinct()->whereNotNull('package')->pluck('package');

        $stats = [
            'total' => DaAccount::count(),
            'active' => DaAccount::where('suspended', false)->count(),
            'suspended' => DaAccount::where('suspended', true)->count(),
            'servers' => $servers->count(),
        ];

        return view('directadmin::user.accounts.index', compact('accounts', 'servers', 'packages', 'stats'));
    }

    public function create(Request $request)
    {
        $servers = DaServer::where('is_active', true)->get();

        if ($servers->isEmpty()) {
            return redirect()->route('user.directadmin.servers.index')
                ->with('info', 'لطفاً ابتدا یک سرور دایرکت ادمین فعال ثبت کنید.');
        }

        $selectedServerId = $request->input('server_id', $servers->first()->id);
        $server = $servers->firstWhere('id', $selectedServerId) ?: $servers->first();

        if ($server->isManual()) {
            $packages = ['Default', 'Standard', 'Pro', 'Unlimited'];
            $ips = [$server->host];
            $serverIp = $server->host;
        } else {
            try {
                $client = new DirectAdminClient(
                    $server->host,
                    $server->username,
                    $server->decrypted_password,
                    $server->port,
                    $server->ssl
                );

                $packages = $client->getPackages();
                $ips = $client->getIpAddresses();
                $serverIp = $ips[0] ?? gethostbyname(preg_replace('/^https?:\/\//i', '', $server->host));
            } catch (\Throwable $e) {
                $packages = ['Default'];
                $ips = [$server->host];
                $serverIp = $server->host;
            }
        }

        $customers = Client::orderBy('full_name')->get();

        return view('directadmin::user.accounts.create', compact('servers', 'server', 'packages', 'ips', 'serverIp', 'customers'));
    }

    public function store(StoreAccountRequest $request)
    {
        $data = $request->validated();
        $server = DaServer::findOrFail($data['da_server_id']);

        if ($server->isManual()) {
            $accountData = [
                'domain' => $data['domain'],
                'email' => $data['email'],
                'package' => $data['package'],
                'ip' => $server->host,
                'suspended' => false,
                'disk_usage_bytes' => 0,
                'disk_limit_bytes' => 0,
                'bandwidth_usage_bytes' => 0,
                'bandwidth_limit_bytes' => 0,
                'domains_count' => 1,
                'email_accounts_count' => 0,
                'mysql_databases_count' => 0,
                'status' => 'active',
                'raw_config' => ['manual' => true],
                'synced_at' => now(),
            ];

            if (Schema::hasColumn('da_accounts', 'client_id')) {
                $accountData['client_id'] = $data['client_id'] ?? null;
            }

            if (Schema::hasColumn('da_accounts', 'order_id')) {
                $accountData['order_id'] = $data['order_id'] ?? null;
            }

            $createdAccount = DaAccount::updateOrCreate(
                [
                    'da_server_id' => $server->id,
                    'username' => $data['username']
                ],
                $accountData
            );

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "اکانت هاستینگ دستی {$data['username']} با موفقیت ثبت و به سفارش متصل شد.",
                    'account' => $createdAccount,
                ]);
            }

            return redirect()->route('user.directadmin.accounts.index')
                ->with('status', "اکانت دستی {$data['username']} با موفقیت ثبت شد.");
        }

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $creation = $client->createUser([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'domain' => $data['domain'],
            'package' => $data['package'],
        ]);

        if (!$creation['success']) {
            return back()->withInput()->with('error', 'خطا در ایجاد اکانت در دایرکت ادمین: ' . ($creation['error'] ?? 'خطای ناشناخته'));
        }

        // Fetch new user config and usage from DirectAdmin to save local cache
        $config = $client->getUserConfig($data['username']);
        $usage = $client->getUserUsage($data['username']);
        $diskUsage = 0;
        $diskLimit = 0;
        $bandwidthUsage = 0;
        $bandwidthLimit = 0;

        if ($usage) {
            $diskUsage = data_get($usage, 'quotaBytes.usage', 0);
            $diskLimit = data_get($usage, 'quotaBytes.limit', 0);
            $bandwidthUsage = data_get($usage, 'bandwidthBytes.usage', 0);
            $bandwidthLimit = data_get($usage, 'bandwidthBytes.limit', 0);
        }

        $accountData = [
            'domain' => $data['domain'],
            'email' => $data['email'],
            'package' => $data['package'],
            'ip' => $config['ip'] ?? null,
            'suspended' => false,
            'disk_usage_bytes' => $diskUsage,
            'disk_limit_bytes' => $diskLimit,
            'bandwidth_usage_bytes' => $bandwidthUsage,
            'bandwidth_limit_bytes' => $bandwidthLimit,
            'domains_count' => count($config['domains'] ?? []),
            'email_accounts_count' => (int)($config['emailAccountsLim'] ?? 0),
            'mysql_databases_count' => (int)($config['mySqlDatabasesLim'] ?? 0),
            'status' => 'active',
            'raw_config' => $config,
            'synced_at' => now(),
        ];

        if (Schema::hasColumn('da_accounts', 'client_id')) {
            $accountData['client_id'] = $data['client_id'] ?? null;
        }

        if (Schema::hasColumn('da_accounts', 'order_id')) {
            $accountData['order_id'] = $data['order_id'] ?? null;
        }

        $createdAccount = DaAccount::updateOrCreate(
            [
                'da_server_id' => $server->id,
                'username' => $data['username']
            ],
            $accountData
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "اکانت هاستینگ {$data['username']} با موفقیت ساخته و به این سفارش متصل شد.",
                'account' => $createdAccount,
            ]);
        }

        return redirect()->route('user.directadmin.accounts.index')
            ->with('status', "اکانت {$data['username']} با موفقیت ساخته و ثبت شد.");
    }

    public function destroy(DaAccount $account)
    {
        $server = $account->server;

        if (!$server->isManual()) {
            $client = new DirectAdminClient(
                $server->host,
                $server->username,
                $server->decrypted_password,
                $server->port,
                $server->ssl
            );

            $deletion = $client->deleteUser($account->username);

            if (!$deletion) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حذف اکانت در وب‌سرویس دایرکت ادمین با خطا مواجه شد. لطفاً اتصال سرور را بررسی نمایید.',
                    ], 422);
                }
                return back()->with('error', 'حذف اکانت در دایرکت ادمین با خطا مواجه شد.');
            }
        }

        $accountId = $account->id;
        $username = $account->username;
        $wasSuspended = $account->suspended;
        $account->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "اکانت هاستینگ {$username} با موفقیت به طور کامل حذف شد.",
                'account_id' => $accountId,
                'was_suspended' => $wasSuspended,
            ]);
        }

        return redirect()->route('user.directadmin.accounts.index')
            ->with('status', 'اکانت هاستینگ مورد نظر با موفقیت حذف شد.');
    }

    public function toggleSuspension(DaAccount $account)
    {
        $server = $account->server;

        if ($server->isManual()) {
            $isSuspended = !$account->suspended;
            $newStatus = $isSuspended ? 'suspended' : 'active';
            $message = $isSuspended
                ? "اکانت دستی {$account->username} با موفقیت مسدود (Suspend) شد."
                : "اکانت دستی {$account->username} با موفقیت فعال (Unsuspend) شد.";
        } else {
            $client = new DirectAdminClient(
                $server->host,
                $server->username,
                $server->decrypted_password,
                $server->port,
                $server->ssl
            );

            if ($account->suspended) {
                $action = $client->unsuspendUser($account->username);
                $message = "اکانت {$account->username} با موفقیت فعال (Unsuspend) شد.";
                $newStatus = 'active';
                $isSuspended = false;
            } else {
                $action = $client->suspendUser($account->username);
                $message = "اکانت {$account->username} با موفقیت مسدود (Suspend) شد.";
                $newStatus = 'suspended';
                $isSuspended = true;
            }

            if (!$action) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تغییر وضعیت تعلیق در دایرکت ادمین با خطا مواجه شد. لطفاً اتصال سرور را بررسی کنید.',
                    ], 422);
                }
                return back()->with('error', 'تغییر وضعیت اکانت در دایرکت ادمین با خطا مواجه شد.');
            }
        }

        $account->update([
            'suspended' => $isSuspended,
            'status' => $newStatus,
        ]);

        // Auto-sync status to associated Order if enabled
        if (DaSetting::get('auto_sync_order_status', true)) {
            if ($account->order_id && class_exists(Status::class)) {
                $orderStatus = Status::where('type', 'order')
                    ->where(function ($q) use ($isSuspended) {
                        if ($isSuspended) {
                            $q->where('name', 'غیر فعال')->orWhere('name', 'like', '%غیرفعال%');
                        } else {
                            $q->where('name', 'فعال');
                        }
                    })->first();

                if ($orderStatus && $account->order) {
                    $account->order()->update(['status_id' => $orderStatus->id]);
                }
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'suspended' => $isSuspended,
                'status' => $newStatus,
                'account_id' => $account->id,
                'username' => $account->username,
            ]);
        }

        return back()->with('status', $message);
    }

    public function sync(SyncAccountsRequest $request)
    {
        $server = DaServer::findOrFail($request->validated('server_id'));

        if ($server->isManual()) {
            return back()->with('status', "سرور «{$server->name}» در حالت دستی (آفلاین) است و اکانت‌های آن به صورت محلی در دیتابیس CRM نگهداری می‌شوند.");
        }

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $usernames = $client->getUsersList();

        if (empty($usernames)) {
            return back()->with('error', 'هیچ کاربری در سرور پیدا نشد یا ارتباط با سرور برقرار نشد.');
        }

        $syncedUsernames = [];

        foreach ($usernames as $username) {
            // Check if the account already exists locally on this server
            $exists = DaAccount::where('da_server_id', $server->id)
                ->where('username', $username)
                ->exists();

            if ($exists) {
                $syncedUsernames[] = $username;
                continue;
            }

            try {
                $config = $client->getUserConfig($username);
                if (!$config) {
                    continue;
                }

                $usage = $client->getUserUsage($username);

                $diskUsage = 0;
                $diskLimit = 0;
                $bandwidthUsage = 0;
                $bandwidthLimit = 0;

                if ($usage) {
                    $diskUsage = data_get($usage, 'quotaBytes.usage', 0);
                    $diskLimit = data_get($usage, 'quotaBytes.limit', 0);
                    $bandwidthUsage = data_get($usage, 'bandwidthBytes.usage', 0);
                    $bandwidthLimit = data_get($usage, 'bandwidthBytes.limit', 0);
                }

                $dateCreated = null;
                if (!empty($config['dateCreated'])) {
                    // Try parsing date string
                    try {
                        $dateCreated = Carbon::parse($config['dateCreated']);
                    } catch (Throwable $e) {
                    }
                }

                $suspended = (bool)($config['suspended'] ?? false);

                DaAccount::create([
                    'da_server_id' => $server->id,
                    'username' => $username,
                    'domain' => $config['domain'] ?? '',
                    'email' => $config['email'] ?? null,
                    'package' => $config['package'] ?? null,
                    'ip' => $config['ip'] ?? null,
                    'suspended' => $suspended,
                    'disk_usage_bytes' => $diskUsage,
                    'disk_limit_bytes' => $diskLimit,
                    'bandwidth_usage_bytes' => $bandwidthUsage,
                    'bandwidth_limit_bytes' => $bandwidthLimit,
                    'domains_count' => count($config['domains'] ?? []),
                    'email_accounts_count' => (int)($config['emailAccountsLim'] ?? 0),
                    'mysql_databases_count' => (int)($config['mySqlDatabasesLim'] ?? 0),
                    'status' => $suspended ? 'suspended' : 'active',
                    'date_created' => $dateCreated,
                    'raw_config' => $config,
                    'synced_at' => now(),
                ]);

                $syncedUsernames[] = $username;
            } catch (Throwable $e) {
                Log::error("[AccountController] Sync failed for user {$username}: " . $e->getMessage());
            }
        }

        // Delete any local accounts that are not on the server anymore
        DaAccount::where('da_server_id', $server->id)
            ->whereNotIn('username', $syncedUsernames)
            ->delete();

        return back()->with('status', 'همگام‌سازی اطلاعات اکانت‌ها با موفقیت انجام شد. تعداد همگام‌سازی شده: ' . count($syncedUsernames));
    }

    /**
     * Update client associated with DirectAdmin account
     */
    public function updateClient(UpdateAccountClientRequest $request, DaAccount $account)
    {
        if (!Schema::hasColumn('da_accounts', 'client_id')) {
            return back()->with('error', 'ستون client_id هنوز روی دیتابیس اجرا نشده است. لطفاً ابتدا میگریشن دیتابیس را اجرا کنید.');
        }

        $account->update([
            'client_id' => $request->validated('client_id'),
        ]);

        return back()->with('status', 'مشتری متصل به اکانت با موفقیت بروزرسانی شد.');
    }

    /**
     * Update or unlink the CRM service order associated with this DirectAdmin account
     */
    public function updateOrder(Request $request, DaAccount $account)
    {
        $orderId = $request->input('order_id');

        if ($orderId) {
            $order = Order::findOrFail($orderId);
            $account->update([
                'order_id' => $order->id,
                'client_id' => $account->client_id ?: $order->customer_id,
            ]);

            // Auto-sync status if enabled
            if (DaSetting::get('auto_sync_order_status', true)) {
                if (class_exists(Status::class)) {
                    $isSuspended = $account->suspended;
                    $orderStatus = Status::where('type', 'order')
                        ->where(function ($q) use ($isSuspended) {
                            if ($isSuspended) {
                                $q->where('name', 'غیر فعال')->orWhere('name', 'like', '%غیرفعال%');
                            } else {
                                $q->where('name', 'فعال');
                            }
                        })->first();

                    if ($orderStatus && $account->order) {
                        $account->order()->update(['status_id' => $orderStatus->id]);
                    }
                }
            }

            return back()->with('status', "اکانت هاست با موفقیت به سفارش شماره {$order->order_number} متصل گردید.");
        } else {
            $account->update(['order_id' => null]);
            return back()->with('status', 'اتصال اکانت هاست از سفارش با موفقیت حذف گردید.');
        }
    }

    /**
     * Link an existing DirectAdmin account to a CRM order (AJAX / Form)
     */
    public function linkOrderAccount(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'account_id' => 'required|exists:da_accounts,id',
        ]);

        $orderId = (int)$request->input('order_id');
        $accountId = (int)$request->input('account_id');

        $order = Order::findOrFail($orderId);
        $account = DaAccount::findOrFail($accountId);

        // Disconnect any other account that was connected to this order
        DaAccount::where('order_id', $orderId)->where('id', '!=', $accountId)->update(['order_id' => null]);

        $account->update([
            'order_id' => $order->id,
            'client_id' => $account->client_id ?: $order->customer_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "اکانت هاستینگ {$account->username} با موفقیت به این سفارش متصل گردید.",
            'account' => $account->load(['server', 'client']),
        ]);
    }

    /**
     * Unlink a DirectAdmin account from a CRM order (AJAX / Form)
     */
    public function unlinkOrderAccount(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'account_id' => 'required|exists:da_accounts,id',
        ]);

        $orderId = (int)$request->input('order_id');
        $accountId = (int)$request->input('account_id');

        $account = DaAccount::where('id', $accountId)->where('order_id', $orderId)->first();
        if ($account) {
            $account->update(['order_id' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => "اتصال اکانت هاستینگ با موفقیت از این سفارش حذف شد.",
        ]);
    }
}
