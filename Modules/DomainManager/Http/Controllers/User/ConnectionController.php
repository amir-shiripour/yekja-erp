<?php

namespace Modules\DomainManager\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\DomainManager\Http\Requests\StoreConnectionRequest;
use Modules\DomainManager\Http\Requests\UpdateConnectionRequest;
use Modules\DomainManager\Services\ResellerWorldClient;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $resellers = DomainReseller::orderBy('id', 'desc')->get();
        $autoOpenAdd = $request->routeIs('*.connections.create') || $request->has('add');
        return view('domainmanager::user.connections.index', compact('resellers', 'autoOpenAdd'));
    }

    public function store(StoreConnectionRequest $request)
    {
        $data = $request->validated();

        $data['is_active'] = $request->has('is_active');
        $data['balance'] = $request->input('balance', 0.00);
        $data['locked_balance'] = $request->input('locked_balance', 0.00);

        if (empty($data['api_key'])) {
            $data['api_key'] = null;
        }

        DomainReseller::create($data);

        return redirect()->route('user.domainmanager.connections.index')
            ->with('status', 'حساب کارگزاری دامنه با موفقیت ثبت شد.');
    }

    public function update(UpdateConnectionRequest $request, DomainReseller $reseller)
    {
        $data = $request->validated();

        $data['is_active'] = $request->has('is_active');
        $data['balance'] = $request->input('balance', 0.00);
        $data['locked_balance'] = $request->input('locked_balance', 0.00);

        if (array_key_exists('api_key', $data) && empty($data['api_key'])) {
            if ($reseller->auth_userid === 'manual' || $request->input('auth_userid') === 'manual') {
                $data['api_key'] = null;
            } else {
                unset($data['api_key']);
            }
        }

        $reseller->update($data);

        return redirect()->back()
            ->with('status', 'تنظیمات کارگزاری با موفقیت بروزرسانی شد.');
    }

    public function destroy(DomainReseller $reseller)
    {
        $reseller->delete();
        return redirect()->route('user.domainmanager.connections.index')
            ->with('status', 'حساب کارگزاری با موفقیت حذف شد.');
    }

    /**
     * AJAX test connection
     */
    public function testConnection(Request $request)
    {
        $authUserId = $request->input('auth_userid');
        $apiKey = $request->input('api_key');
        $resellerId = $request->input('reseller_id');

        if ($resellerId) {
            $reseller = DomainReseller::find($resellerId);
            if ($reseller) {
                if (empty($authUserId)) {
                    $authUserId = $reseller->auth_userid;
                }
                if (empty($apiKey)) {
                    $apiKey = $reseller->decrypted_api_key;
                }
            }
        }

        if ($authUserId === 'manual') {
            if (!empty($resellerId)) {
                $reseller = DomainReseller::find($resellerId);
                if ($reseller) {
                    $reseller->update([
                        'last_connected_at' => now(),
                        'last_error' => null,
                    ]);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'کارگزار دستی (آفلاین) فعال و آماده استفاده است.'
            ]);
        }

        if (empty($authUserId) || empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'لطفاً تمامی فیلدهای الزامی را پر کنید.'
            ]);
        }

        $client = new ResellerWorldClient($authUserId, $apiKey);
        $result = $client->testConnection();

        if ($result['success']) {
            // Update balance in db
            $balanceData = $result['balance'] ?? [];
            $balance = (float) ($balanceData['sellingbalance'] ?? $balanceData['balance'] ?? 0.0);
            
            if ($resellerId) {
                $reseller = DomainReseller::find($resellerId);
                if ($reseller) {
                    $reseller->update([
                        'balance' => $balance,
                        'last_connected_at' => now(),
                        'last_error' => null,
                    ]);
                }
            }
        } elseif (!$result['success'] && $resellerId) {
            $reseller = DomainReseller::find($resellerId);
            if ($reseller) {
                $reseller->update([
                    'last_error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }

        return response()->json($result);
    }
}
