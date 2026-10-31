@php
    use Carbon\Carbon;
    use Modules\ResellerHub\App\Enums\PackageColor;
    use Modules\ResellerHub\App\Enums\PackageDiscountType;
    use Modules\ResellerHub\App\Enums\PackageIcon;
    use Modules\ResellerHub\App\Enums\PackagePriceType;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Morilog\Jalali\Jalalian;

    $defaultColor = PackageColor::INDIGO->hex();
    $defaultIcon = PackageIcon::TOOTH->value;
    $defaultPriceType = PackagePriceType::SUBSCRIPTION->value;
    $defaultDiscountType = PackageDiscountType::AMOUNT->value;
@endphp
@extends('layouts.user')

@section('title', 'ویرایش پکیج: ' . $package->name)

@php
    $resellerId = $reseller->id ?? (auth()->user()?->reseller?->id ?? 0);
    $currency = $currency ?? ResellerSetting::getCurrencyForReseller($resellerId);
    $currencyLabel = $currencyLabel ?? ResellerSetting::getCurrencyLabelForReseller($resellerId);

    $defaultActivePeriods = is_array($package->active_periods ?? null) && !empty($package->active_periods)
        ? $package->active_periods
        : ['monthly_price', 'quarterly_price', 'semi_annual_price', 'yearly_price'];

    $formattedPeriodDiscounts = [];
    $rawDiscounts = old('period_discounts', $package->period_discounts ?? []);
    if (is_array($rawDiscounts)) {
        foreach ($rawDiscounts as $m => $cfg) {
            if (!is_array($cfg)) continue;
            $startsJalali = '';
            if (!empty($cfg['discount_starts_at'])) {
                try {
                    $startsJalali = Jalalian::fromCarbon(Carbon::parse($cfg['discount_starts_at']))->format('Y/m/d');
                } catch (Throwable $e) {
                    $startsJalali = (string)$cfg['discount_starts_at'];
                }
            }
            $expiresJalali = '';
            if (!empty($cfg['discount_expires_at'])) {
                try {
                    $expiresJalali = Jalalian::fromCarbon(Carbon::parse($cfg['discount_expires_at']))->format('Y/m/d');
                } catch (Throwable $e) {
                    $expiresJalali = (string)$cfg['discount_expires_at'];
                }
            }
            $hasCustom = !empty($cfg['has_custom']) || ((float)($cfg['discount_value'] ?? 0) > 0);
            $formattedPeriodDiscounts[(string)$m] = [
                'has_custom' => (bool)$hasCustom,
                'discount_value' => (float)($cfg['discount_value'] ?? 0),
                'discount_type' => $cfg['discount_type'] ?? $defaultDiscountType,
                'discount_starts_at' => $startsJalali,
                'discount_expires_at' => $expiresJalali,
            ];
        }
    }
@endphp

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('partials.jalali-date-picker')

    @include('resellerhub::user.packages.partials.edit.script')

    <div x-data="packageEditManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">
        @include('resellerhub::user.packages.partials.edit.header')

        <form action="{{ route('reseller.packages.update', $package->id) }}" method="POST" @submit="submitting = true"
              class="space-y-8">
            @csrf
            @method('PUT')
            {{-- Hidden Inputs --}}
            <input type="hidden" name="color" :value="selectedColor">
            <input type="hidden" name="icon" :value="selectedIcon">
            <input type="hidden" name="price_type" :value="pricing.price_type">

            {{-- Dynamic Active Periods & Period Prices from Rows --}}
            <template x-for="r in periodRows" :key="'hidden-act-' + r.id">
                <input type="hidden" name="active_periods[]" :value="resolvePeriodKey(r.months)">
            </template>
            <template x-for="r in periodRows" :key="'hidden-prc-' + r.id">
                <input type="hidden" :name="'period_prices[' + r.months + ']'"
                       :value="pricing.price_type === 'subscription' ? getPeriodPrice(r.months) : 0">
            </template>
            <template x-for="r in periodRows" :key="'hidden-dsc-' + r.id">
                <template x-if="pricing.price_type === 'subscription'">
                    <div>
                        <input type="hidden" :name="'period_discounts[' + r.months + '][has_custom]'"
                               :value="getPeriodDiscount(r.months).has_custom ? '1' : '0'">
                        <template x-if="getPeriodDiscount(r.months).has_custom">
                            <div>
                                <input type="hidden" :name="'period_discounts[' + r.months + '][discount_value]'"
                                       :value="getPeriodDiscount(r.months).discount_value">
                                <input type="hidden" :name="'period_discounts[' + r.months + '][discount_type]'"
                                       :value="getPeriodDiscount(r.months).discount_type || 'amount'">
                                <input type="hidden" :name="'period_discounts[' + r.months + '][discount_starts_at]'"
                                       :value="getPeriodDiscount(r.months).discount_starts_at || ''">
                                <input type="hidden" :name="'period_discounts[' + r.months + '][discount_expires_at]'"
                                       :value="getPeriodDiscount(r.months).discount_expires_at || ''">
                            </div>
                        </template>
                    </div>
                </template>
            </template>
            {{-- Legacy Fields Compatibility for 1, 3, 6, 12 --}}
            <template x-for="m in [1, 3, 6, 12]" :key="'legacy-field-' + m">
                <input type="hidden" :name="resolvePeriodKey(m)"
                       :value="(pricing.price_type === 'subscription' && isMonthActive(m)) ? getPeriodPrice(m) : 0">
            </template>

            <input type="hidden" name="one_time_price"
                   :value="pricing.price_type === 'one_time' ? pricing.one_time_price : 0">
            <input type="hidden" name="setup_fee" :value="pricing.setup_fee">
            <input type="hidden" name="discount_type" value="{{ $defaultDiscountType }}">
            <input type="hidden" name="discount_value"
                   :value="pricing.price_type !== 'free' ? pricing.discount_value : 0">

            <template x-for="feat in selectedFeatures" :key="feat">
                <input type="hidden" name="features[]" :value="feat">
            </template>

            @include('resellerhub::user.packages.partials.edit.card-basic')
            @include('resellerhub::user.packages.partials.edit.card-modules')
            @include('resellerhub::user.packages.partials.edit.card-pricing')
            @include('resellerhub::user.packages.partials.edit.actions')
    </form>
</div>
@endsection
