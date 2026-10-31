<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

class CustomFieldController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', CustomUserField::ENTITY_USER);
        if (!in_array($tab, [CustomUserField::ENTITY_USER, CustomUserField::ENTITY_SUB_ACCOUNT], true)) {
            $tab = CustomUserField::ENTITY_USER;
        }

        // بررسی نصب و فعال بودن ماژول ریسیلرهاب و پرمیشن مربوطه
        $isResellerHubEnabled = Module::has('ResellerHub') && Module::isEnabled('ResellerHub');
        $canAccessSubAccounts = $isResellerHubEnabled && (auth()->user()->can('custom-fields.sub-accounts.view') || auth()->user()->hasRole('super-admin'));

        if ($tab === CustomUserField::ENTITY_SUB_ACCOUNT && !$canAccessSubAccounts) {
            $tab = CustomUserField::ENTITY_USER;
        }

        $query = CustomUserField::query()
            ->where('entity_type', $tab);

        if ($tab === CustomUserField::ENTITY_USER) {
            $query->with('role')->orderBy('role_name');
        } else {
            $query->orderBy('id', 'desc');
        }

        $fields = $query->paginate(20)->withQueryString();

        $userFieldsCount = CustomUserField::where('entity_type', CustomUserField::ENTITY_USER)->count();
        $subAccountFieldsCount = $canAccessSubAccounts
            ? CustomUserField::where('entity_type', CustomUserField::ENTITY_SUB_ACCOUNT)->count()
            : 0;

        return view('admin.custom_fields.index', compact('fields', 'tab', 'userFieldsCount', 'subAccountFieldsCount'));
    }

    public function create(Request $request)
    {
        $entityType = $request->query('entity_type', CustomUserField::ENTITY_USER);

        // هدایت خودکار در صورت درخواست ساخت فیلد برای زیرمجموعه
        if ($entityType === CustomUserField::ENTITY_SUB_ACCOUNT) {
            return redirect()->route('admin.custom-fields.sub-account.create');
        }

        // نقش‌ها را از Spatie بارگذاری کنید
        $allRoles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('display_name', 'name');

        // اگر کاربر ادمین است (نه سوپر ادمین)، نقش super-admin را از لیست حذف کن
        $roles = $allRoles;
        if (!auth()->user()->hasRole('super-admin')) {
            $roles = $allRoles->except('super-admin');
        }

        return view('admin.custom_fields.create', compact('roles'));
    }

    public function createSubAccount(Request $request)
    {
        $isResellerHubEnabled = Module::has('ResellerHub') && Module::isEnabled('ResellerHub');
        $canAccessSubAccounts = $isResellerHubEnabled && (auth()->user()->can('custom-fields.sub-accounts.view') || auth()->user()->hasRole('super-admin'));

        if (!$canAccessSubAccounts) {
            abort(403, 'شما دسترسی به تعریف فیلدهای احراز هویت زیرمجموعه‌ها را ندارید.');
        }

        $entityType = CustomUserField::ENTITY_SUB_ACCOUNT;

        return view('admin.custom_fields.sub_account_create', compact('entityType'));
    }

    public function store(Request $request)
    {
        $entityType = $request->input('entity_type', CustomUserField::ENTITY_USER);
        if (!in_array($entityType, [CustomUserField::ENTITY_USER, CustomUserField::ENTITY_SUB_ACCOUNT], true)) {
            $entityType = CustomUserField::ENTITY_USER;
        }

        $rules = [
            'entity_type' => ['required', 'string', 'in:user,sub_account'],
            'label'       => ['required', 'string', 'max:255'],
            'field_type'  => ['required', 'string'],
            'is_required' => ['nullable', 'boolean'],
        ];

        if ($entityType === CustomUserField::ENTITY_USER) {
            $rules['role_name'] = ['required', 'string'];
        } else {
            $rules['role_name'] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        if ($entityType === CustomUserField::ENTITY_SUB_ACCOUNT) {
            $data['role_name'] = 'sub_account';
        } else {
            // جلوگیری از ایجاد فیلد برای نقش super-admin توسط ادمین عادی
            if (!auth()->user()->hasRole('super-admin') && $data['role_name'] === 'super-admin') {
                return back()
                    ->withErrors(['role_name' => 'شما نمی‌توانید فیلد برای نقش super-admin ایجاد کنید.'])
                    ->withInput();
            }
        }

        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        $data['field_name'] = $this->makeUniqueKey(
            $request->input('field_name') ?: $data['label'],
            $data['role_name'],
            $entityType
        );

        CustomUserField::create($data);

        return redirect()->route('admin.custom-fields.index', ['tab' => $entityType])
            ->with('success', 'فیلد سفارشی با موفقیت ایجاد شد.');
    }

    public function edit(CustomUserField $field)
    {
        $entityType = $field->entity_type ?? CustomUserField::ENTITY_USER;

        if ($entityType === CustomUserField::ENTITY_SUB_ACCOUNT) {
            $isResellerHubEnabled = Module::has('ResellerHub') && Module::isEnabled('ResellerHub');
            $canAccessSubAccounts = $isResellerHubEnabled && (auth()->user()->can('custom-fields.sub-accounts.view') || auth()->user()->hasRole('super-admin'));

            if (!$canAccessSubAccounts) {
                abort(403, 'شما دسترسی به ویرایش فیلدهای احراز هویت زیرمجموعه‌ها را ندارید.');
            }

            return view('admin.custom_fields.sub_account_edit', compact('field', 'entityType'));
        }

        $allRoles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('display_name', 'name');

        $roles = $allRoles;
        if (!auth()->user()->hasRole('super-admin')) {
            $roles = $allRoles->except('super-admin');
        }

        return view('admin.custom_fields.edit', compact('field', 'roles', 'entityType'));
    }

    public function update(Request $request, CustomUserField $field)
    {
        $entityType = $request->input('entity_type', $field->entity_type ?? CustomUserField::ENTITY_USER);

        $rules = [
            'label'       => ['required', 'string', 'max:255'],
            'field_type'  => ['required', 'string'],
            'is_required' => ['nullable', 'boolean'],
        ];

        if ($entityType === CustomUserField::ENTITY_USER) {
            $rules['role_name'] = ['required', 'string'];
        } else {
            $rules['role_name'] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        if ($entityType === CustomUserField::ENTITY_SUB_ACCOUNT) {
            $data['role_name'] = 'sub_account';
        } else {
            if (!auth()->user()->hasRole('super-admin') && $data['role_name'] === 'super-admin') {
                return back()
                    ->withErrors(['role_name' => 'شما نمی‌توانید نقش فیلد را به super-admin تغییر دهید.'])
                    ->withInput();
            }
        }

        $data['entity_type'] = $entityType;
        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        $desiredKey = $request->input('field_name') ?: $data['label'];
        $needsNewKey = $desiredKey !== $field->field_name || $data['role_name'] !== $field->role_name || $entityType !== $field->entity_type;

        if ($needsNewKey) {
            $data['field_name'] = $this->makeUniqueKey($desiredKey, $data['role_name'], $entityType, $field->id);
        } else {
            $data['field_name'] = $field->field_name;
        }

        $field->update($data);

        return redirect()->route('admin.custom-fields.index', ['tab' => $entityType])
            ->with('success', 'فیلد سفارشی با موفقیت بروزرسانی شد.');
    }

    public function destroy(CustomUserField $field)
    {
        $entityType = $field->entity_type ?? CustomUserField::ENTITY_USER;
        $field->delete();
        return redirect()->route('admin.custom-fields.index', ['tab' => $entityType])
            ->with('success', 'فیلد با موفقیت حذف شد.');
    }

    /**
     * تولید کلید یکتا (snake_case) براساس label/desiredKey برای هر role_name و entity_type.
     */
    private function makeUniqueKey(string $desiredKey, string $roleName, string $entityType = 'user', ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($desiredKey), '_');
        if ($base === '' || $base === null) {
            $base = 'field';
        }

        $reserved = ['name', 'email', 'password', 'role', 'subdomain', 'mobile'];
        if (in_array($base, $reserved, true)) {
            $base = $base . '_cf';
        }

        $key = $base;
        $i = 1;

        while (
            CustomUserField::where('role_name', $roleName)
                ->where('entity_type', $entityType)
                ->where('field_name', $key)
                ->when($ignoreId, fn($q) => $q->where('id', '<>', $ignoreId))
                ->exists()
        ) {
            $key = $base . '_' . $i;
            $i++;
        }

        return $key;
    }
}
