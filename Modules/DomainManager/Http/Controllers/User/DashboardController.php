<?php

namespace Modules\DomainManager\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\DomainManager\Entities\DomainRecord;
use Modules\DomainManager\Services\ResellerWorldClient;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $resellers = DomainReseller::where('is_active', true)->get();

        if ($resellers->isEmpty()) {
            return redirect()->route('user.domainmanager.connections.index')
                ->with('info', 'لطفاً ابتدا یک حساب کارگزاری دامنه ثبت و فعال کنید.');
        }

        $selectedResellerId = $request->input('reseller_id', $resellers->first()->id);
        $reseller = $resellers->firstWhere('id', $selectedResellerId) ?: $resellers->first();

        // Get count stats
        $totalActive = DomainRecord::where('domain_reseller_id', $reseller->id)
            ->where('status', 'active')
            ->count();

        $totalExpired = DomainRecord::where('domain_reseller_id', $reseller->id)
            ->where('status', 'expired')
            ->count();

        $totalPending = DomainRecord::where('domain_reseller_id', $reseller->id)
            ->where('status', 'pending')
            ->count();

        // Get domains expiring soon (next 30 days)
        $expiringSoon = DomainRecord::where('domain_reseller_id', $reseller->id)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->take(5)
            ->get();

        $liveBalance = $reseller->balance;
        $lockedBalance = $reseller->locked_balance;

        return view('domainmanager::user.dashboard.index', compact(
            'resellers',
            'reseller',
            'totalActive',
            'totalExpired',
            'totalPending',
            'expiringSoon',
            'liveBalance',
            'lockedBalance'
        ));
    }
}
