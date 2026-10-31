<?php

namespace Modules\ResellerHub\App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerSubAccount;

class StoreResellerInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Resolve the acting reseller id for the current request.
     *
     * Mirrors the resolution used by the HasReseller trait so validation and
     * business logic never disagree about "whose data is this".
     */
    protected function resolveResellerId(): ?int
    {
        $user = $this->user();
        if (!$user) {
            return null;
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if ($isSuperAdmin) {
            $requested = $this->input('reseller_id') ?: session('active_reseller_id');

            if ($requested && $requested !== 'all') {
                return (int)$requested;
            }

            $own = Reseller::where('user_id', $user->id)->value('id');

            return $own ? (int)$own : null;
        }

        $id = $user->reseller?->id;

        return $id ? (int)$id : null;
    }


    protected function prepareForValidation(): void
    {
        $resellerId = $this->resolveResellerId();

        if ($this->filled('sub_account_id')) {
            $sub = ResellerSubAccount::query()
                ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                ->find($this->input('sub_account_id'));

            if ($sub) {
                $this->merge([
                    'sub_account_name' => $sub->name,
                    'sub_account_mobile' => $sub->mobile,
                    'sub_account_email' => $sub->email,
                ]);
            }
        } elseif ($this->filled('tenant_id')) {
            $tenant = Tenant::with('subAccount')
                ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                ->find($this->input('tenant_id'));

            if ($tenant && $tenant->subAccount) {
                $this->merge([
                    'sub_account_id' => $tenant->subAccount->id,
                    'sub_account_name' => $tenant->subAccount->name,
                    'sub_account_mobile' => $tenant->subAccount->mobile,
                    'sub_account_email' => $tenant->subAccount->email,
                ]);
            }
        }
    }

    public function rules(): array
    {
        $resellerId = $this->resolveResellerId();
        $subAccountExists = Rule::exists('reseller_sub_accounts', 'id');
        $tenantExists = Rule::exists('tenants', 'id');
        $packageExists = Rule::exists('reseller_packages', 'id');

        if ($resellerId) {
            $subAccountExists->where('reseller_id', $resellerId);
            $tenantExists->where('reseller_id', $resellerId);
            $packageExists->where('reseller_id', $resellerId);
        }

        return [
            'sub_account_id' => ['required_without:tenant_id', 'nullable', 'integer', $subAccountExists],
            'tenant_id' => ['nullable', 'integer', $tenantExists],
            'package_id' => ['nullable', 'integer', $packageExists],
            'type' => ['nullable', 'string', 'in:official'],
            'status' => ['nullable', 'string', 'in:draft,unpaid,paid,overdue,cancelled'],
            'issue_date' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.title' => ['required', 'string', 'max:200'],
            'items.*.item_type' => ['nullable', 'string', 'in:package,module,feature,custom'],
            'items.*.item_key' => ['nullable', 'string', 'max:150'],
            'items.*.duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'integer', 'min:0'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'sub_account_id.required_without' => 'انتخاب زیرمجموعه الزامی است.',
            'sub_account_id.exists' => 'زیرمجموعه انتخاب‌شده معتبر نمی‌باشد.',
            'tenant_id.exists' => 'سامانه انتخاب‌شده معتبر نمی‌باشد.',
            'package_id.exists' => 'پکیج انتخاب‌شده معتبر نمی‌باشد.',
            'items.required' => 'حداقل یک قلم کالا یا خدمات باید در فاکتور ثبت گردد.',
            'items.min' => 'حداقل یک قلم کالا یا خدمات باید در فاکتور ثبت گردد.',
            'items.*.title.required' => 'عنوان قلم فاکتور الزامی است.',
            'items.*.unit_price.required' => 'قیمت واحد قلم الزامی است.',
            'items.*.unit_price.min' => 'قیمت واحد نمی‌تواند منفی باشد.',
            'tax_percent.max' => 'درصد مالیات نمی‌تواند بیش از ۱۰۰ درصد باشد.',
        ];
    }
}
