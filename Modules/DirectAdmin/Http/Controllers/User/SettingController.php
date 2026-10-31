<?php

namespace Modules\DirectAdmin\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaSetting;
use Modules\Services\App\Http\Models\ServiceCategory;
use Throwable;

class SettingController extends Controller
{
    public function index()
    {
        $servers = DaServer::orderBy('name')->get();
        $autoSyncStatus = (bool)DaSetting::get('auto_sync_order_status', true);
        $defaultServerId = DaSetting::get('default_server_id', $servers->first()?->id);

        // Check if the hosting category exists in Services module
        $hostingCategory = null;
        if (class_exists(ServiceCategory::class)) {
            $hostingCategory = ServiceCategory::where('slug', 'hosting')->first();
        }

        return view('directadmin::user.settings.index', compact(
            'servers',
            'autoSyncStatus',
            'defaultServerId',
            'hostingCategory'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'default_server_id' => 'nullable|exists:da_servers,id',
            'auto_sync_order_status' => 'nullable|boolean',
            'manual_server_name' => 'nullable|string|max:191',
            'manual_server_host' => 'nullable|string|max:191',
            'manual_server_port' => 'nullable|integer|min:1|max:65535',
            'manual_server_username' => 'nullable|string|max:191',
            'manual_server_description' => 'nullable|string|max:500',
        ]);

        DaSetting::set('auto_sync_order_status', $request->has('auto_sync_order_status') ? '1' : '0');

        if ($request->input('server_selection_mode') === 'type' && $request->filled('manual_server_name')) {
            $name = trim((string)$request->input('manual_server_name'));
            $host = trim((string)$request->input('manual_server_host', 'localhost')) ?: 'localhost';
            $port = (int)$request->input('manual_server_port', 2222) ?: 2222;
            $username = trim((string)$request->input('manual_server_username', 'manual')) ?: 'manual';

            $server = DaServer::create([
                'name' => $name,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => null,
                'ssl' => false,
                'is_active' => true,
                'metadata' => [
                    'type' => 'manual',
                    'description' => $request->input('manual_server_description', 'سرور پیش فرض برای سفارشات دستی'),
                ],
            ]);

            DaSetting::set('default_server_id', $server->id);
        } elseif ($request->filled('default_server_id')) {
            DaSetting::set('default_server_id', $request->input('default_server_id'));
        }

        return back()
            ->with('status', 'تنظیمات ماژول دایرکت ادمین با موفقیت ذخیره شد.')
            ->with('active_tab', $request->input('active_tab', 'general'));
    }

    public function seedCategory(Request $request)
    {
        try {
            if (!class_exists(ServiceCategory::class)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ماژول خدمات و سرویس‌ها (Services) در سیستم فعال نمی‌باشد.',
                ], 422);
            }

            // Hosting System Category
            $category = ServiceCategory::updateOrCreate(
                ['slug' => 'hosting'],
                [
                    'name' => 'میزبانی وب و هاستینگ',
                    'slug' => 'hosting',
                    'color' => '#059669',
                    'icon' => 'server-stack',
                    'description' => 'دسته‌بندی اختصاصی سرویس‌های هاستینگ و وب‌سرورهای دایرکت ادمین',
                    'status' => 'active',
                    'is_locked' => true,
                    'sort_order' => 1,
                ]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'دسته‌بندی قفل‌شده «میزبانی وب و هاستینگ» با موفقیت در سیستم ثبت و فعال شد.',
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'is_locked' => (bool)$category->is_locked,
                    ],
                ]);
            }

            return back()->with('status', 'دسته‌بندی قفل‌شده هاستینگ با موفقیت ایجاد گردید.');
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
