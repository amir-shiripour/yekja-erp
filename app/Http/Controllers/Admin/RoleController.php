<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\PermissionCatalog;
use App\Support\WidgetRegistry;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\WidgetSetting;

class RoleController extends Controller
{
    public function index()
    {
        $query = Role::query();

        // اگر کاربر لاگین شده super-admin نیست، نقش super-admin را از لیست حذف کن
        if (!auth()->user()->hasRole('super-admin')) {
            $query->where('name', '!=', 'super-admin');
        }

        $roles = $query->when(
            Schema::hasColumn('roles', 'display_name'),
            fn($q) => $q->orderBy('display_name')->orderBy('name'),
            fn($q) => $q->orderBy('name')
        )
            ->get();

        $roleUserCounts = [];
        foreach ($roles as $role) {
            $roleUserCounts[$role->name] = DB::table('model_has_roles')
                ->where('role_id', $role->id)->count();
        }

        return view('admin.roles.index', compact('roles', 'roleUserCounts'));
    }

    public function create()
    {
        $permissions   = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);

        // 🔹 همه ویجت‌های ثبت‌شده از Registry
        $widgets = WidgetRegistry::all();

        return view('admin.roles.create', compact('permissions', 'permissionGroups', 'widgets'));
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = Str::slug(Str::ascii($base));
            if ($slug === '') {
                $slug = 'role';
            }
        }

        $original = $slug;
        $i = 2;

        $exists = fn(string $candidate) => Role::where('name', $candidate)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        while ($exists($slug)) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();

        $slug = $data['name'] ?? null;
        if (!$slug) {
            $slug = $this->makeUniqueSlug($data['display_name'] ?? '');
        }

        // جلوگیری از ایجاد نقش با نام super-admin توسط ادمین عادی
        if (!auth()->user()->hasRole('super-admin') && $slug === 'super-admin') {
            return back()
                ->withErrors(['name' => 'شما نمی‌توانید نقش با نام super-admin ایجاد کنید.'])
                ->withInput();
        }

        $role = Role::create([
            'name'         => $slug,
            'display_name' => $data['display_name'] ?? null,
            'guard_name'   => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        // 🔹 ذخیره تنظیمات ویجت‌ها
        $widgetsInput = $request->input('widgets', []);
        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id'    => $role->id,
                'widget_key' => $widgetKey,
                'is_active'  => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        // جلوگیری از ویرایش نقش super-admin توسط ادمین عادی
        if (!auth()->user()->hasRole('super-admin') && $role->name === 'super-admin') {
            abort(403, 'شما نمی‌توانید نقش super-admin را ویرایش کنید.');
        }

        $permissions   = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);
        $selected = $role->permissions()->pluck('name')->toArray();

        // 🔹 همه ویجت‌های موجود
        $widgets = WidgetRegistry::all();

        // 🔹 ویجت‌های فعال برای این نقش
        $roleWidgets = WidgetSetting::where('role_id', $role->id)
            ->where('is_active', true)
            ->pluck('widget_key')
            ->toArray();

        // ویجت تقویم و رویدادها برای نقش‌های مدیریتی (نقش admin یا نقش ۱۰) همیشه به‌صورت پیش‌فرض فعال باشد
        if (in_array($role->name, ['admin', 'super-admin']) || (int)$role->id === 10) {
            if (!in_array('calendar_widget', $roleWidgets, true)) {
                $roleWidgets[] = 'calendar_widget';
                WidgetSetting::updateOrCreate(
                    ['role_id' => $role->id, 'widget_key' => 'calendar_widget'],
                    ['is_active' => true]
                );
            }
        }

        return view('admin.roles.edit', compact(
            'role',
            'permissions',
            'permissionGroups',
            'selected',
            'widgets',
            'roleWidgets'
        ));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        // جلوگیری از ویرایش نقش super-admin توسط ادمین عادی
        if (!auth()->user()->hasRole('super-admin') && $role->name === 'super-admin') {
            abort(403, 'شما نمی‌توانید نقش super-admin را ویرایش کنید.');
        }

        $data = $request->validated();

        $incomingSlug = $data['name'] ?? null;
        if (!$incomingSlug) {
            $incomingSlug = $this->makeUniqueSlug(
                $data['display_name'] ?? $role->display_name ?? $role->name,
                $role->id
            );
        }

        // جلوگیری از تغییر نام نقش super-admin
        if ($role->name === 'super-admin' && $incomingSlug !== 'super-admin') {
            return back()
                ->withErrors(['name' => 'نقش super-admin قابل تغییر نام نیست.'])
                ->withInput();
        }

        // جلوگیری از تغییر نام یک نقش دیگر به super-admin توسط ادمین عادی
        if (!auth()->user()->hasRole('super-admin') && $incomingSlug === 'super-admin') {
            return back()
                ->withErrors(['name' => 'شما نمی‌توانید نام نقش را به super-admin تغییر دهید.'])
                ->withInput();
        }

        $role->update([
            'name'         => $incomingSlug,
            'display_name' => $data['display_name'] ?? $role->display_name,
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        // 🔹 بروزرسانی ویجت‌ها
        $widgetsInput = $request->input('widgets', []);

        // ویجت تقویم و رویدادها برای مدیران سامانه (admin یا نقش ۱۰) همیشه تضمین‌شده فعال بماند
        if (in_array($role->name, ['admin', 'super-admin']) || (int)$role->id === 10) {
            $widgetsInput['calendar_widget'] = 'on';
        }

        WidgetSetting::where('role_id', $role->id)->delete();

        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id'    => $role->id,
                'widget_key' => $widgetKey,
                'is_active'  => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        // جلوگیری از حذف نقش super-admin
        if ($role->name === 'super-admin') {
            return back()->withErrors(['role' => 'نقش super-admin قابل حذف نیست.']);
        }

        // جلوگیری از حذف نقش super-admin توسط ادمین عادی (برای اطمینان)
        if (!auth()->user()->hasRole('super-admin') && $role->name === 'super-admin') {
            abort(403, 'شما نمی‌توانید نقش super-admin را حذف کنید.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش حذف شد.');
    }
}
