<?php

namespace Modules\DirectAdmin\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaAccount;
use Modules\DirectAdmin\Http\Requests\ChangeAccountPasswordRequest;
use Modules\DirectAdmin\Http\Requests\ModifyAccountRequest;
use Modules\DirectAdmin\Services\DirectAdminClient;
use Modules\Services\App\Http\Models\Order;

class AccountDetailController extends Controller
{
    public function show(string $username)
    {
        $account = DaAccount::where('username', $username)->firstOrFail();
        $server = $account->server;

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $connection = $client->testConnection();
        $config = $connection['success'] ? $client->getUserConfig($username) : null;
        $cmdConfig = $connection['success'] ? $client->getUserCmdConfig($username) : null;

        if ($config && $cmdConfig) {
            $config = array_merge($config, $cmdConfig);
        }

        $usage = $connection['success'] ? $client->getUserUsage($username) : null;

        // Fallback to local cache if offline
        if (!$config) {
            $config = $account->raw_config ?: [];
        }

        $packages = $connection['success'] ? $client->getPackages() : [];
        $wpLocations = $connection['success'] ? $client->getWordPressLocations($account->domain) : [];

        $account->load(['server', 'client', 'order.service.category', 'order.customer']);

        $allOrdersList = [];
        if (class_exists(Order::class)) {
            $allOrdersList = Order::whereHas('service.category', function ($q) {
                $q->where('slug', 'hosting');
            })
                ->with(['customer', 'service.category'])
                ->latest()
                ->get();
        }

        return view('directadmin::user.accounts.show', [
            'account' => $account,
            'config' => $config,
            'usage' => $usage,
            'packages' => $packages,
            'wpLocations' => $wpLocations,
            'connection' => $connection,
            'allOrdersList' => $allOrdersList,
        ]);
    }

    public function changePassword(ChangeAccountPasswordRequest $request, string $username)
    {
        $account = DaAccount::where('username', $username)->firstOrFail();
        $server = $account->server;

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $changed = $client->changePassword($username, $request->validated('password'));

        if (!$changed) {
            return back()->with('error', 'تغییر رمز عبور هاست در دایرکت ادمین با خطا مواجه شد.');
        }

        return back()->with('status', 'رمز عبور هاست با موفقیت در دایرکت ادمین تغییر یافت.');
    }

    public function modify(ModifyAccountRequest $request, string $username)
    {
        $account = DaAccount::where('username', $username)->firstOrFail();
        $server = $account->server;

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $modifyType = $request->validated('modify_type', 'package');
        $payload = [];

        if ($modifyType === 'package') {
            $payload['package'] = $request->validated('package');
        } else {
            // Manual custom settings
            if ($request->filled('quota')) $payload['quota'] = $request->input('quota');
            if ($request->filled('bandwidth')) $payload['bandwidth'] = $request->input('bandwidth');
            if ($request->filled('vdomains')) $payload['vdomains'] = $request->input('vdomains');
            if ($request->filled('nemailf')) $payload['nemailf'] = $request->input('nemailf');
            if ($request->filled('mysql')) $payload['mysql'] = $request->input('mysql');
            if ($request->filled('ftps')) $payload['ftps'] = $request->input('ftps');

            // Booleans / Checkboxes
            $payload['php'] = $request->has('php') ? 'ON' : 'OFF';
            $payload['cgi'] = $request->has('cgi') ? 'ON' : 'OFF';
            $payload['ssl'] = $request->has('ssl') ? 'ON' : 'OFF';
            $payload['ssh'] = $request->has('ssh') ? 'ON' : 'OFF';
            $payload['dnscontrol'] = $request->has('dnscontrol') ? 'ON' : 'OFF';
        }

        $res = $client->modifyUser($username, $payload);

        if (!$res['success']) {
            return back()->with('error', 'خطا در ویرایش کاربر: ' . ($res['error'] ?? 'خطای ناشناخته'));
        }

        if (isset($payload['package'])) {
            $account->update(['package' => $payload['package']]);
        }

        return back()->with('status', 'تنظیمات کاربر با موفقیت در دایرکت ادمین به‌روزرسانی شد.');
    }

    public function loginHistory(string $username)
    {
        $account = DaAccount::where('username', $username)->firstOrFail();
        $server = $account->server;

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        $connection = $client->testConnection();
        $history = $connection['success'] ? $client->getUserLoginHistory($username) : [];

        return view('directadmin::user.login-history.index', compact('account', 'history', 'connection'));
    }
}
