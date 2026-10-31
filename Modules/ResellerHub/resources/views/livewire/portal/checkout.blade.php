@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Livewire\Portal\Checkout;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    $currencyLabel = $currencyLabel ?? reseller_currency_label();
    $urlScheme = $urlScheme ?? (request()?->getScheme() ? request()?->getScheme() . '://' : 'https://');
    $colorPresets = $colorPresets ?? ResellerHelper::getColorPresets();
@endphp
<div
    class="flex min-h-screen flex-col items-center bg-slate-50/70 p-4 sm:p-6 lg:p-8 dark:bg-gray-950 font-sans antialiased">
    <div class="mb-8 w-full max-w-6xl pt-2 sm:pt-6">
        {{-- Header & Stepper Indicator --}}
        @include('resellerhub::livewire.portal.checkout-partials.header-stepper')

        @if($step === 1)
            <div class="space-y-8">
                @include('resellerhub::livewire.portal.checkout-partials.step-1-package')
                @include('resellerhub::livewire.portal.checkout-partials.step-2-domain')
                @include('resellerhub::livewire.portal.checkout-partials.step-3-branding')
            </div>
        @endif

        @if($step === 2 && $selectedPackage)
            @include('resellerhub::livewire.portal.checkout-partials.step-4-payment')
        @endif

        <style>
            /* Fix for x-cloak flash */
            [x-cloak] {
                display: none !important;
            }

            /* Hide scrollbar but keep functionality */
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
        @include('resellerhub::user.packages.partials.icons-script')
    </div>
</div>
