<?php

namespace Modules\DomainManager\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\Services\App\Http\Models\ServiceCategory;
use Throwable;

class SettingController extends Controller
{
    /**
     * Settings index view
     */
    public function index()
    {
        $resellers = DomainReseller::where('is_active', true)->get();

        // Check if the domain category exists in Services module
        $domainCategory = null;
        if (class_exists(ServiceCategory::class)) {
            $domainCategory = ServiceCategory::where('slug', 'domain')->first();
        }

        return view('domainmanager::user.settings.index', compact(
            'resellers',
            'domainCategory'
        ));
    }

    /**
     * Seed or update the locked domain category in Services module
     */
    public function seedCategory(Request $request)
    {
        try {
            if (!class_exists(ServiceCategory::class)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ماژول خدمات و سرویس‌ها (Services) در سیستم فعال نمی‌باشد.',
                ], 422);
            }

            // Domain System Category
            $category = ServiceCategory::updateOrCreate(
                ['slug' => 'domain'],
                [
                    'name' => 'ثبت و تمدید دامنه',
                    'slug' => 'domain',
                    'color' => '#4F46E5',
                    'icon' => 'globe-alt',
                    'description' => 'دسته‌بندی اختصاصی و سیستمی سفارشات ثبت، تمدید و انتقال دامنه',
                    'status' => 'active',
                    'is_locked' => true,
                    'sort_order' => 2,
                ]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'دسته‌بندی قفل‌شده «ثبت و تمدید دامنه» با موفقیت در ماژول خدمات ثبت و فعال شد.',
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'is_locked' => (bool)$category->is_locked,
                    ],
                ]);
            }

            return back()->with('status', 'دسته‌بندی قفل‌شده دامنه با موفقیت در ماژول خدمات ایجاد گردید.');
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در ثبت دسته‌بندی سیستمی: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'خطا در ثبت دسته‌بندی سیستمی: ' . $e->getMessage());
        }
    }
}
