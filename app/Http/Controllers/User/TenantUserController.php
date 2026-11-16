<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Concerns\HandlesCustomUserFields;
use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use App\Models\User;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantRoleBoundary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class TenantUserController extends Controller
{
    use HandlesCustomUserFields;

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

        return (int) $tenantId;
    }

    /**
     * Get roles allowed to be assigned by tenant managers to their staff.
     */
    protected function getTenantAllowedRoles()
    {
        return $this->roleBoundary->getAllowedRoles();
    }

    public function index(Request $request)
    {
        $tenantId = $this->getTenantId();
        $allowedRoleKeys = $this->getTenantAllowedRoles()->keys()->all();

        $users = User::with('roles')
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($allowedRoleKeys) {
                $query->whereDoesntHave('roles', function ($q) use ($allowedRoleKeys) {
                    $q->whereNotIn('name', $allowedRoleKeys);
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'indexRoute' => 'user.users.index',
            'createRoute' => 'user.users.create',
            'editRoute' => 'user.users.edit',
            'pageTitle' => 'مدیریت کاربران',
            'pageSubtitle' => 'مشاهده و مدیریت کاربران سیستم و دسترسی‌های آن‌ها',
        ]);
    }

    public function create()
    {
        $tenantId = $this->getTenantId();

        $user = new User();
        $roles = $this->getTenantAllowedRoles();

        $allUsers = User::where('id', '!=', $user->id)
            ->where('tenant_id', $tenantId)
            ->get(['id', 'name']);

        $selectedRoles = [];
        $customFieldsByRole = CustomUserField::orderBy('role_name')->get()->groupBy('role_name');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'selectedRoles' => $selectedRoles,
            'customFieldsByRole' => $customFieldsByRole,
            'allUsers' => $allUsers,
            'indexRoute' => 'user.users.index',
            'storeRoute' => 'user.users.store',
            'pageTitle' => 'ایجاد کاربر جدید',
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = $this->getTenantId();

        $request->merge([
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'mobile' => $request->mobile ? trim($request->mobile) : null,
        ]);

        $roles = $request->input('roles', []);
        if (!$this->roleBoundary->areRolesAllowed($roles)) {
            return back()->withInput()->withErrors(['roles.0' => 'انتساب نقش‌های فراتر از سقف دسترسی سازمان مجاز نمی‌باشد.']);
        }

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['nullable', 'string', 'max:20', Rule::unique('users', 'mobile')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($this->getTenantAllowedRoles()->keys()->all())],
            'superior_ids' => ['nullable', 'array'],
            'superior_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
        ];

        $fields = $this->fieldsForRoles($roles);
        $dynamicRules = $this->dynamicRules($fields);
        $rules = array_merge($baseRules, $dynamicRules);

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $tenantId, $fields, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'] ?? null,
                'password' => Hash::make($validated['password']),
                'tenant_id' => $tenantId,
            ]);

            $user->syncRoles($validated['roles']);

            if (!empty($validated['superior_ids'])) {
                $user->superiors()->sync($validated['superior_ids']);
            }

            $this->persistCustomValues($user, $fields, $request);
        });

        return redirect()->route('user.users.index')->with('success', 'کاربر جدید با موفقیت ایجاد شد.');
    }

    public function edit(User $user)
    {
        $tenantId = $this->getTenantId();

        if ((int) $user->tenant_id !== $tenantId) {
            abort(404);
        }

        if ($this->roleBoundary->isUserOutsideCeiling($user)) {
            abort(403, 'ویرایش کاربر ارشد یا خارج از سقف سازمان از این بخش امکان‌پذیر نیست.');
        }

        $roles = $this->getTenantAllowedRoles();
        $selectedRoles = $user->roles()->pluck('name')->toArray();

        $allUsers = User::where('id', '!=', $user->id)
            ->where('tenant_id', $tenantId)
            ->get(['id', 'name']);

        $customFieldsByRole = CustomUserField::orderBy('role_name')->get()->groupBy('role_name');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'selectedRoles' => $selectedRoles,
            'customFieldsByRole' => $customFieldsByRole,
            'allUsers' => $allUsers,
            'indexRoute' => 'user.users.index',
            'updateRoute' => 'user.users.update',
            'destroyRoute' => 'user.users.destroy',
            'pageTitle' => 'ویرایش کاربر: ' . $user->name,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $tenantId = $this->getTenantId();

        if ((int) $user->tenant_id !== $tenantId) {
            abort(404);
        }

        if ($this->roleBoundary->isUserOutsideCeiling($user)) {
            abort(403, 'ویرایش کاربر ارشد یا خارج از سقف سازمان از این بخش امکان‌پذیر نیست.');
        }

        $request->merge([
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'mobile' => $request->mobile ? trim($request->mobile) : null,
        ]);

        $roles = $request->input('roles', []);
        if (!$this->roleBoundary->areRolesAllowed($roles)) {
            return back()->withInput()->withErrors(['roles.0' => 'انتساب نقش‌های فراتر از سقف دسترسی سازمان مجاز نمی‌باشد.']);
        }

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($this->getTenantAllowedRoles()->keys()->all())],
            'superior_ids' => ['nullable', 'array'],
            'superior_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('id', '!=', $user->id)),
            ],
        ];

        $fields = $this->fieldsForRoles($roles);
        $dynamicRules = $this->dynamicRules($fields);
        $rules = array_merge($baseRules, $dynamicRules);

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $user, $tenantId, $fields, $request) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->mobile = $validated['mobile'] ?? null;
            $user->tenant_id = $tenantId;

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->syncRoles($validated['roles']);
            $user->superiors()->sync($validated['superior_ids'] ?? []);

            $this->persistCustomValues($user, $fields, $request);
        });

        return redirect()->route('user.users.index')->with('success', 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(User $user)
    {
        $tenantId = $this->getTenantId();

        if ((int) $user->tenant_id !== $tenantId) {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('user.users.index')->with('error', 'نمی‌توانید حساب کاربری خودتان را حذف کنید.');
        }

        if ($this->roleBoundary->isUserOutsideCeiling($user)) {
            abort(403, 'حذف کاربر ارشد یا خارج از سقف سازمان از این بخش امکان‌پذیر نیست.');
        }

        $user->delete();

        return redirect()->route('user.users.index')->with('success', 'کاربر با موفقیت حذف شد.');
    }
}
