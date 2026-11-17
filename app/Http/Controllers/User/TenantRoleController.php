<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Support\PermissionCatalog;
use App\Support\WidgetRegistry;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantRoleBoundary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class TenantRoleController extends Controller
{
    protected TenantRoleBoundary $roleBoundary;

    public function __construct(TenantRoleBoundary $roleBoundary)
    {
        $this->roleBoundary = $roleBoundary;
    }

    /**
     * Resolve the current tenant id with strict fallback.
     */
    protected function getTenantId(): int
    {
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->hasTenant() ? $tenantManager->getTenant() : null;

        if (!$tenant && app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
        }

        if (!$tenant && view()->shared('currentTenant')) {
            $tenant = view()->shared('currentTenant');
        }

        $tenantId = $tenant?->id ?? auth()->user()?->tenant_id;

        if (!$tenantId) {
            abort(403, 'دسترسی غیرمجاز. شناسه تننت معتبر یافت نشد.');
        }

        return (int)$tenantId;
    }

    /**
     * Resolve the current tenant model.
     */
    protected function getTenant(): ?Tenant
    {
        $tenantManager = app(TenantManager::class);
        if ($tenantManager->hasTenant()) {
            return $tenantManager->getTenant();
        }

        if (app()->bound('currentTenant')) {
            return app('currentTenant');
        }

        $tenantId = $this->getTenantId();
        return Tenant::find($tenantId);
    }

    public function index()
    {
        $tenantId = $this->getTenantId();
        $allowedRoleNames = $this->roleBoundary->getAllowedRoles($tenantId)->keys()->all();

        $roles = Role::whereIn('name', $allowedRoleNames)
            ->when(
                Schema::hasColumn('roles', 'display_name'),
                fn($q) => $q->orderBy('display_name')->orderBy('name'),
                fn($q) => $q->orderBy('name')
            )
            ->get();

        $roleUserCounts = [];
        foreach ($roles as $role) {
            $roleUserCounts[$role->name] = DB::table('model_has_roles')
                ->join('users', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->where('model_has_roles.role_id', $role->id)
                ->where('users.tenant_id', $tenantId)
                ->count();
        }

        return view('admin.roles.index', [
            'roles' => $roles,
            'roleUserCounts' => $roleUserCounts,
            'isTenantContext' => true,
            'createRoute' => 'user.roles.create',
            'editRoute' => 'user.roles.edit',
            'destroyRoute' => 'user.roles.destroy',
            'indexRoute' => 'user.roles.index',
            'currentTenantId' => $tenantId,
        ]);
    }

    public function create()
    {
        $tenant = $this->getTenant();
        $allowedPermissions = $this->roleBoundary->getAllowedPermissions($tenant);

        $permissions = $allowedPermissions->pluck('name')->toArray();
        $permissionGroups = PermissionCatalog::groupAndTranslate($allowedPermissions);
        $widgets = WidgetRegistry::all();

        return view('admin.roles.create', [
            'permissions' => $permissions,
            'permissionGroups' => $permissionGroups,
            'widgets' => $widgets,
            'isTenantContext' => true,
            'storeRoute' => 'user.roles.store',
            'indexRoute' => 'user.roles.index',
        ]);
    }

    protected function makeUniqueTenantSlug(string $base, int $tenantId, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = Str::slug(Str::ascii($base));
            if ($slug === '') {
                $slug = 'custom-role';
            }
        }

        $candidate = "t{$tenantId}_{$slug}";
        $original = $candidate;
        $i = 2;

        $exists = fn(string $name) => Role::where('name', $name)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        while ($exists($candidate)) {
            $candidate = "{$original}-{$i}";
            $i++;
        }

        return $candidate;
    }

    public function store(Request $request)
    {
        $tenantId = $this->getTenantId();
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'widgets' => ['nullable', 'array'],
        ]);

        // Filter permissions strictly through tenant boundary
        $allowedPerms = $this->roleBoundary->getAllowedPermissions($tenant)->pluck('name')->toArray();
        $safePermissions = array_intersect($validated['permissions'] ?? [], $allowedPerms);

        $baseSlug = !empty($validated['name']) ? $validated['name'] : $validated['display_name'];
        $slug = $this->makeUniqueTenantSlug($baseSlug, $tenantId);

        $role = Role::create([
            'name' => $slug,
            'display_name' => $validated['display_name'],
            'guard_name' => 'web',
            'tenant_id' => $tenantId,
        ]);

        if (!empty($safePermissions)) {
            $role->syncPermissions($safePermissions);
        }

        // Widgets configuration
        $widgetsInput = $request->input('widgets', []);
        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id' => $role->id,
                'widget_key' => $widgetKey,
                'is_active' => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('user.roles.index')->with('success', 'نقش سفارشی با موفقیت در این سازمان ایجاد شد.');
    }

    public function edit(Role $role)
    {
        $tenantId = $this->getTenantId();
        $tenant = $this->getTenant();

        // Security Guard: Cannot edit roles belonging to other tenants or system global template roles
        if ((int)$role->tenant_id !== $tenantId) {
            abort(403, 'ویرایش نقش‌های عمومی سامانه یا سایر سازمان‌ها امکان‌پذیر نیست.');
        }

        $allowedPermissions = $this->roleBoundary->getAllowedPermissions($tenant);
        $permissions = $allowedPermissions->pluck('name')->toArray();
        $permissionGroups = PermissionCatalog::groupAndTranslate($allowedPermissions);
        $selected = $role->permissions()->pluck('name')->toArray();
        $widgets = WidgetRegistry::all();

        $roleWidgets = WidgetSetting::where('role_id', $role->id)
            ->where('is_active', true)
            ->pluck('widget_key')
            ->toArray();

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'permissionGroups' => $permissionGroups,
            'selected' => $selected,
            'widgets' => $widgets,
            'roleWidgets' => $roleWidgets,
            'isTenantContext' => true,
            'updateRoute' => 'user.roles.update',
            'indexRoute' => 'user.roles.index',
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $tenantId = $this->getTenantId();
        $tenant = $this->getTenant();

        if ((int)$role->tenant_id !== $tenantId) {
            abort(403, 'ویرایش نقش‌های عمومی سامانه یا سایر سازمان‌ها امکان‌پذیر نیست.');
        }

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'widgets' => ['nullable', 'array'],
        ]);

        $allowedPerms = $this->roleBoundary->getAllowedPermissions($tenant)->pluck('name')->toArray();
        $safePermissions = array_intersect($validated['permissions'] ?? [], $allowedPerms);

        $role->update([
            'display_name' => $validated['display_name'],
        ]);

        $role->syncPermissions($safePermissions);

        // Update widgets
        $widgetsInput = $request->input('widgets', []);
        WidgetSetting::where('role_id', $role->id)->delete();
        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id' => $role->id,
                'widget_key' => $widgetKey,
                'is_active' => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('user.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        $tenantId = $this->getTenantId();

        if ((int)$role->tenant_id !== $tenantId) {
            abort(403, 'حذف نقش‌های عمومی سامانه یا سایر سازمان‌ها امکان‌پذیر نیست.');
        }

        // Check if assigned to any user
        $hasUsers = DB::table('model_has_roles')
            ->join('users', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.role_id', $role->id)
            ->where('users.tenant_id', $tenantId)
            ->exists();

        if ($hasUsers) {
            return back()->withErrors(['role' => 'این نقش به کاربرانی در این سازمان تخصیص داده شده است و ابتدا باید نقش آن‌ها تغییر یابد.']);
        }

        WidgetSetting::where('role_id', $role->id)->delete();
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('user.roles.index')->with('success', 'نقش سفارشی با موفقیت حذف شد.');
    }
}
