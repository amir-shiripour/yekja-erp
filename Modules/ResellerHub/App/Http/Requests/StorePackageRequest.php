<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Throwable;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $cleanNumber = fn($val) => (int) ResellerHelper::cleanNumber($val);
        $cleanDecimal = fn($val) => (float) ResellerHelper::cleanNumber(str_replace(['٪', '%'], '', (string)$val));

        $priceType = $this->input('price_type', 'subscription');
        if (!in_array($priceType, ['subscription', 'one_time', 'free'], true)) {
            $priceType = 'subscription';
        }

        $discountType = 'amount';
        $discountValue = $cleanNumber($this->discount_value);

        $activePeriods = $this->input('active_periods', []);
        if (is_string($activePeriods)) {
            $decoded = json_decode($activePeriods, true);
            $activePeriods = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($activePeriods)) {
            $activePeriods = [];
        }

        $cleanActivePeriods = [];
        foreach ($activePeriods as $p) {
            $m = ResellerPackage::resolveMonthFromPeriodKey($p);
            if ($m >= 1) {
                $cleanActivePeriods[] = ResellerPackage::resolvePeriodKeyFromMonth($m);
            }
        }
        $activePeriods = array_values(array_unique($cleanActivePeriods));

        $rawPeriodPrices = $this->input('period_prices', []);
        if (is_string($rawPeriodPrices)) {
            $rawPeriodPrices = json_decode($rawPeriodPrices, true) ?: [];
        }
        $cleanPeriodPrices = [];
        if (is_array($rawPeriodPrices)) {
            foreach ($rawPeriodPrices as $m => $v) {
                $month = ResellerPackage::resolveMonthFromPeriodKey($m);
                $pVal = $cleanNumber($v);
                if ($month >= 1 && $pVal > 0) {
                    $cleanPeriodPrices[(string)$month] = $pVal;
                }
            }
        }

        // Also check individual period inputs (e.g. period_2_price, monthly_price, etc.)
        foreach ($this->all() as $inputKey => $inputVal) {
            if (preg_match('/^period_(\d+)_price$/', (string)$inputKey, $matches)) {
                $m = (int)$matches[1];
                $pVal = $cleanNumber($inputVal);
                if ($m >= 1 && $pVal > 0) {
                    $cleanPeriodPrices[(string)$m] = $pVal;
                }
            }
        }

        $monthlyPrice = $cleanPeriodPrices['1'] ?? $cleanNumber($this->monthly_price);
        $quarterlyPrice = $cleanPeriodPrices['3'] ?? $cleanNumber($this->quarterly_price);
        $semiAnnualPrice = $cleanPeriodPrices['6'] ?? $cleanNumber($this->semi_annual_price);
        $yearlyPrice = $cleanPeriodPrices['12'] ?? $cleanNumber($this->yearly_price);

        $discountStartsAt = $this->filled('discount_starts_at')
            ? reseller_to_carbon($this->input('discount_starts_at'))?->startOfDay()
            : null;

        $discountExpiresAt = $this->filled('discount_expires_at')
            ? reseller_to_carbon($this->input('discount_expires_at'))?->endOfDay()
            : null;

        $rawPeriodDiscounts = $this->input('period_discounts', []);
        if (is_string($rawPeriodDiscounts)) {
            $rawPeriodDiscounts = json_decode($rawPeriodDiscounts, true) ?: [];
        }
        $cleanPeriodDiscounts = [];
        if (is_array($rawPeriodDiscounts)) {
            foreach ($rawPeriodDiscounts as $m => $cfg) {
                if (!is_array($cfg)) {
                    continue;
                }
                $month = ResellerPackage::resolveMonthFromPeriodKey($m);
                if ($month < 1) {
                    continue;
                }

                $hasCustom = !empty($cfg['has_custom']) && in_array((string)$cfg['has_custom'], ['1', 'true', 'on'], true);
                if (!$hasCustom) {
                    continue;
                }

                $dVal = $cleanNumber($cfg['discount_value'] ?? 0);
                if ($dVal <= 0) {
                    continue;
                }

                $dType = 'amount';

                $dStartsAt = !empty($cfg['discount_starts_at'])
                    ? reseller_to_carbon($cfg['discount_starts_at'])?->startOfDay()->toDateTimeString()
                    : null;

                $dExpiresAt = !empty($cfg['discount_expires_at'])
                    ? reseller_to_carbon($cfg['discount_expires_at'])?->endOfDay()->toDateTimeString()
                    : null;

                $cleanPeriodDiscounts[(string)$month] = [
                    'has_custom' => true,
                    'discount_value' => $dVal,
                    'discount_type' => $dType,
                    'discount_starts_at' => $dStartsAt,
                    'discount_expires_at' => $dExpiresAt,
                ];
            }
        }

        $this->merge([
            'price_type' => $priceType,
            'active_periods' => $activePeriods,
            'period_prices' => $cleanPeriodPrices,
            'period_discounts' => $cleanPeriodDiscounts,
            'monthly_price' => $monthlyPrice,
            'quarterly_price' => $quarterlyPrice,
            'semi_annual_price' => $semiAnnualPrice,
            'yearly_price' => $yearlyPrice,
            'one_time_price' => $cleanNumber($this->one_time_price),
            'setup_fee' => $cleanNumber($this->setup_fee),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_starts_at' => $discountStartsAt,
            'discount_expires_at' => $discountExpiresAt,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string'],
            'limits' => ['nullable', 'array'],
            'limits.*' => ['nullable'],

            'price_type' => ['required', 'in:subscription,one_time,free'],
            'active_periods' => ['nullable', 'array'],
            'active_periods.*' => ['string'],
            'period_prices' => ['nullable', 'array'],
            'period_discounts' => ['nullable', 'array'],

            'monthly_price' => ['nullable', 'integer', 'min:0'],
            'quarterly_price' => ['nullable', 'integer', 'min:0'],
            'semi_annual_price' => ['nullable', 'integer', 'min:0'],
            'yearly_price' => ['nullable', 'integer', 'min:0'],
            'period_2_price' => ['nullable', 'integer', 'min:0'],
            'period_4_price' => ['nullable', 'integer', 'min:0'],
            'period_5_price' => ['nullable', 'integer', 'min:0'],
            'period_7_price' => ['nullable', 'integer', 'min:0'],
            'period_8_price' => ['nullable', 'integer', 'min:0'],
            'period_9_price' => ['nullable', 'integer', 'min:0'],
            'period_10_price' => ['nullable', 'integer', 'min:0'],
            'period_11_price' => ['nullable', 'integer', 'min:0'],
            'one_time_price' => ['nullable', 'integer', 'min:0'],
            'setup_fee' => ['nullable', 'integer', 'min:0'],

            'discount_type' => ['nullable', 'in:amount'],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'discount_expires_at' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام پکیج الزامی است.',
            'name.max' => 'نام پکیج نمی‌تواند بیش از ۱۰۰ کاراکتر باشد.',
            'description.max' => 'توضیحات پکیج نمی‌تواند بیش از ۵۰۰ کاراکتر باشد.',
            'modules.required' => 'حداقل باید یک ماژول برای این پکیج انتخاب فرمایید.',
            'modules.min' => 'حداقل باید یک ماژول برای این پکیج انتخاب فرمایید.',
            'modules.array' => 'فرمت ماژول‌های انتخابی نامعتبر است.',
            'price_type.required' => 'انتخاب نوع پکیج (دوره‌ای، یک‌باره یا رایگان) الزامی است.',
            'price_type.in' => 'نوع پکیج انتخاب‌شده نامعتبر است.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $priceType = $this->input('price_type', 'subscription');

            if ($priceType === 'subscription') {
                $periodPrices = (array)$this->input('period_prices', []);
                $periodDiscounts = (array)$this->input('period_discounts', []);
                $globalDiscount = (int)$this->input('discount_value', 0);

                $resolveBasePrice = function ($m) use ($periodPrices) {
                    $m = (string)$m;
                    if (isset($periodPrices[$m]) && (int)$periodPrices[$m] > 0) {
                        return (int)$periodPrices[$m];
                    }
                    if ($m === '1' && (int)$this->input('monthly_price') > 0) return (int)$this->input('monthly_price');
                    if ($m === '3' && (int)$this->input('quarterly_price') > 0) return (int)$this->input('quarterly_price');
                    if ($m === '6' && (int)$this->input('semi_annual_price') > 0) return (int)$this->input('semi_annual_price');
                    if ($m === '12' && (int)$this->input('yearly_price') > 0) return (int)$this->input('yearly_price');
                    return (int)($this->input("period_{$m}_price") ?? 0);
                };

                foreach ($periodDiscounts as $m => $cfg) {
                    if (!is_array($cfg) || empty($cfg['has_custom'])) {
                        continue;
                    }
                    $month = ResellerPackage::resolveMonthFromPeriodKey($m);
                    $discountVal = (int)($cfg['discount_value'] ?? 0);
                    if ($discountVal <= 0) {
                        continue;
                    }

                    $basePrice = $resolveBasePrice($month);
                    if ($basePrice <= 0) {
                        $validator->errors()->add(
                            "period_discounts.{$month}.discount_value",
                            "برای دوره {$month} ماهه، ابتدا باید تعرفه قیمت تعیین فرمایید."
                        );
                    } elseif ($discountVal > $basePrice) {
                        $formattedPrice = number_format($basePrice);
                        $validator->errors()->add(
                            "period_discounts.{$month}.discount_value",
                            "مبلغ تخفیف دوره {$month} ماهه نمی‌تواند بیشتر از تعرفه قیمت آن ({$formattedPrice} تومان) باشد."
                        );
                    }
                }

                if ($globalDiscount > 0) {
                    foreach ($periodPrices as $m => $priceVal) {
                        $month = ResellerPackage::resolveMonthFromPeriodKey($m);
                        $basePrice = (int)$priceVal;
                        $hasCustom = !empty($periodDiscounts[(string)$month]['has_custom']) || !empty($periodDiscounts[$m]['has_custom']);
                        if (!$hasCustom && $basePrice > 0 && $globalDiscount > $basePrice) {
                            $formattedPrice = number_format($basePrice);
                            $validator->errors()->add(
                                'discount_value',
                                "مبلغ تخفیف عمومی پکیج نمی‌تواند بیشتر از تعرفه دوره {$month} ماهه ({$formattedPrice} تومان) باشد."
                            );
                            break;
                        }
                    }
                }
            } elseif ($priceType === 'one_time') {
                $oneTimePrice = (int)$this->input('one_time_price', 0);
                $globalDiscount = (int)$this->input('discount_value', 0);

                if ($globalDiscount > 0) {
                    if ($oneTimePrice <= 0) {
                        $validator->errors()->add(
                            'discount_value',
                            'ابتدا تعرفه خرید یک‌باره را تعیین فرمایید.'
                        );
                    } elseif ($globalDiscount > $oneTimePrice) {
                        $formattedPrice = number_format($oneTimePrice);
                        $validator->errors()->add(
                            'discount_value',
                            "مبلغ تخفیف پکیج نمی‌تواند بیشتر از تعرفه خرید یک‌باره ({$formattedPrice} تومان) باشد."
                        );
                    }
                }
            }
        });
    }
}
