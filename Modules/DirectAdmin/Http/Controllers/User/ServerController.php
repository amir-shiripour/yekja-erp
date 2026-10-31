<?php

namespace Modules\DirectAdmin\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaSetting;
use Modules\DirectAdmin\Http\Requests\StoreServerRequest;
use Modules\DirectAdmin\Http\Requests\UpdateServerRequest;
use Modules\DirectAdmin\Services\DirectAdminClient;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        $servers = DaServer::orderBy('id', 'desc')->get();
        // Check if route is for creating a new server, or if add query param is set
        $autoOpenAdd = $request->routeIs('*.servers.create') || $request->has('add');
        return view('directadmin::user.servers.index', compact('servers', 'autoOpenAdd'));
    }

    public function store(StoreServerRequest $request)
    {
        $data = $request->validated();
        $data['ssl'] = $request->has('ssl');
        $data['is_active'] = $request->has('is_active');

        $isManual = ($request->input('server_type') === 'manual') || ($data['username'] === 'manual');
        if ($isManual) {
            $data['password'] = !empty($data['password']) ? $data['password'] : null;
            $data['metadata'] = [
                'type' => 'manual',
                'description' => $request->input('description', $data['name']),
            ];
        }

        $server = DaServer::create($data);

        if ($request->boolean('set_as_default') || DaServer::count() === 1) {
            DaSetting::set('default_server_id', $server->id);
        }

        return redirect()->route('user.directadmin.servers.index')
            ->with('status', 'سرور با موفقیت ثبت شد.');
    }

    public function setDefault(DaServer $server)
    {
        DaSetting::set('default_server_id', $server->id);
        return back()->with('status', "سرور «{$server->name}» با موفقیت به عنوان سرور پیش‌فرض انتخاب شد.");
    }

    public function update(UpdateServerRequest $request, DaServer $server)
    {
        $data = $request->validated();
        $data['ssl'] = $request->has('ssl');
        $data['is_active'] = $request->has('is_active');

        $isManual = ($request->input('server_type') === 'manual') || ($data['username'] === 'manual');
        if ($isManual) {
            $data['metadata'] = array_merge($server->metadata ?? [], [
                'type' => 'manual',
                'description' => $request->input('description', $data['name']),
            ]);
            if (empty($data['password'])) {
                $data['password'] = null;
            }
        } elseif (empty($data['password'])) {
            unset($data['password']);
        }

        $server->update($data);

        return redirect()->route('user.directadmin.servers.index')
            ->with('status', 'تنظیمات سرور با موفقیت بروزرسانی شد.');
    }

    public function destroy(DaServer $server)
    {
        $server->delete();
        return redirect()->route('user.directadmin.servers.index')
            ->with('status', 'سرور دایرکت ادمین با موفقیت حذف شد.');
    }

    /**
     * AJAX action to test connectivity
     */
    public function testConnection(Request $request)
    {
        $host = $request->input('host');
        $port = (int)$request->input('port', 2222);
        $username = $request->input('username');
        $password = $request->input('password');
        $ssl = $request->boolean('ssl', true);
        $serverId = $request->input('server_id');

        if (empty($password) && $serverId) {
            $server = DaServer::find($serverId);
            if ($server) {
                $password = $server->decrypted_password;
            }
        }

        if ($username === 'manual') {
            if ($serverId) {
                $server = DaServer::find($serverId);
                if ($server) {
                    $server->update([
                        'last_connected_at' => now(),
                        'last_error' => null,
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'سرور دستی (آفلاین) فعال و آماده استفاده است.'
            ]);
        }

        if (empty($host) || empty($username) || empty($password)) {
            return response()->json([
                'success' => false,
                'error' => 'لطفاً تمامی فیلدهای الزامی را پر کنید.'
            ]);
        }

        $client = new DirectAdminClient($host, $username, $password, $port, $ssl);
        $result = $client->testConnection();

        if ($result['success'] && $serverId) {
            $server = DaServer::find($serverId);
            if ($server) {
                $server->update([
                    'last_connected_at' => now(),
                    'last_error' => null,
                ]);
            }
        } elseif (!$result['success'] && $serverId) {
            $server = DaServer::find($serverId);
            if ($server) {
                $server->update([
                    'last_error' => $result['error'] ?? 'Unknown connection error',
                ]);
            }
        }

        return response()->json($result);
    }
}
