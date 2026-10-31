<?php

namespace Modules\DirectAdmin\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaAccount;
use Modules\DirectAdmin\Services\DirectAdminClient;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $servers = DaServer::where('is_active', true)->get();

        if ($servers->isEmpty()) {
            return redirect()->route('user.directadmin.servers.index')
                ->with('info', 'لطفاً ابتدا یک سرور دایرکت ادمین ثبت کنید.');
        }

        // Selected server (default to first one)
        $selectedServerId = $request->input('server_id', $servers->first()->id);
        $server = $servers->firstWhere('id', $selectedServerId) ?: $servers->first();

        $client = new DirectAdminClient(
            $server->host,
            $server->username,
            $server->decrypted_password,
            $server->port,
            $server->ssl
        );

        // Fetch live stats with graceful failovers
        $connection = $client->testConnection();
        $serverState = $connection['success'] ? $client->getServerState() : null;
        $resellerConfig = $connection['success'] ? $client->getResellerConfig() : null;
        $resellerUsage = $connection['success'] ? $client->getResellerUsage() : null;

        // Local statistics
        $localStats = [
            'total_accounts' => DaAccount::where('da_server_id', $server->id)->count(),
            'active_accounts' => DaAccount::where('da_server_id', $server->id)->where('suspended', false)->count(),
            'suspended_accounts' => DaAccount::where('da_server_id', $server->id)->where('suspended', true)->count(),
        ];

        // Update server connectivity info in database
        if ($connection['success']) {
            $server->update([
                'last_connected_at' => now(),
                'last_error' => null,
            ]);
        } else {
            $server->update([
                'last_error' => $connection['error'] ?? 'Unknown connection error',
            ]);
        }

        return view('directadmin::user.dashboard.index', [
            'servers' => $servers,
            'server' => $server,
            'connection' => $connection,
            'serverState' => $serverState,
            'resellerConfig' => $resellerConfig,
            'resellerUsage' => $resellerUsage,
            'localStats' => $localStats,
        ]);
    }
}
