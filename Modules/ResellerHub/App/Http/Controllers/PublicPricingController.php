<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerPackage;

class PublicPricingController extends Controller
{
    /**
     * Display public package pricing page.
     */
    public function index(): View
    {
        $packages = ResellerPackage::active()->orderBy('monthly_price')->paginate(9);

        $availablePeriods = ResellerHelper::getAllAvailablePeriods();
        $defaultPeriod = 'monthly_price';

        return view('resellerhub::public.pricing', compact(
            'packages',
            'availablePeriods',
            'defaultPeriod'
        ));
    }
}
