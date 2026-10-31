@php use Modules\ResellerHub\App\Helpers\ResellerHelper; @endphp
<div role="tabpanel" id="tabpanel-package_upgrade" aria-labelledby="tab-package_upgrade" x-show="activeTab === 'package_upgrade'" x-cloak class="space-y-8">
    <x-resellerhub::card>
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-indigo-50/70 to-transparent dark:from-indigo-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="p-2.5 bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 rounded-2xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">پکیج و ماژول‌های
                        فعال کنونی سفارش</h3>
                    <p class="text-xs text-gray-400 mt-0.5">مشخصات لایسنس جاری و دسترسی‌های فعال روی سامانه
                        خریدار</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1.5 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}
                        </span>
                <span
                    class="px-3 py-1.5 rounded-xl text-xs font-black {{ $remainingDaysCount > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-400 border border-rose-200 dark:border-rose-800' }}">
                            {{ $remainingDaysCount > 0 ? (reseller_fa_num($remainingDaysCount) . ' روز مانده') : 'منقضی شده' }}
                        </span>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-bold text-gray-400 block mb-1.5">عنوان پکیج فعال</span>
                    <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0"
                                      style="background-color: {{ $package?->color ?: '#10b981' }}"></span>
                        <h4 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">{{ $package?->name ?? 'پکیج سفارشی (اختصاصی)' }}</h4>
                    </div>
                    @if($package?->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $package->description }}</p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">ماژول‌ها و دسترسی‌های این سفارش
                            به صورت اختصاصی و سفارشی تنظیم شده‌اند.</p>
                    @endif
                </div>

                <div
                    class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-bold text-gray-400 block mb-1.5">هزینه اشتراک سفارش</span>
                    <div class="font-black text-lg sm:text-xl text-gray-900 dark:text-white tabular-nums">
                        {{ reseller_price($order->total_amount, $currencyLabel) }}
                    </div>
                    <span
                        class="text-xs text-gray-400 mt-1 block">دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}</span>
                </div>

                <div
                    class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-bold text-gray-400 block mb-1.5">سامانه متصل (CRM)</span>
                    @if($tenant)
                        <div
                            class="font-black text-sm sm:text-base text-indigo-600 dark:text-indigo-400 truncate">
                            {{ $tenant->name }}
                        </div>
                        <span
                            class="text-xs text-gray-400 dir-ltr text-start block mt-0.5">{{ $tenant->subdomain }}.*</span>
                    @else
                        <span class="text-xs text-gray-400">سامانه‌ای متصل نشده است</span>
                    @endif
                </div>
            </div>

            {{-- Active Modules List --}}
            @php
                $tab2Modules = $order->modules_snapshot ?? [];

                // Fallback: derive the snapshot from the tenant's provisioned modules.
                if (empty($tab2Modules) && $tenant && $tenant->relationLoaded('modules') && $tenant->modules->isNotEmpty()) {
                    $tab2Modules = $tenant->modules->map(fn($m) => [
                        'title' => null,
                        'slug' => $m->module_slug ?? $m->slug,
                        'type' => 'ماژول',
                    ])->toArray();
                }

                /**
                 * Snapshots created by older releases stored the English module name
                 * (e.g. "Notifications"). Always resolve the Persian label from the slug
                 * and only fall back to a stored title when it is already localized.
                 */
                $resolveModuleLabel = static function ($item): string {
                    $slug = is_array($item)
                        ? (string)($item['slug'] ?? $item['key'] ?? '')
                        : '';
                    $stored = is_array($item)
                        ? (string)($item['title'] ?? '')
                        : (string)$item;

                    $resolved = $slug !== ''
                        ? ResellerHelper::getModuleTitle($slug)
                        : '';

                    foreach ([$resolved, $stored] as $candidate) {
                        if ($candidate !== '' && preg_match('/\p{Arabic}/u', $candidate) === 1) {
                            return $candidate;
                        }
                    }

                    return $resolved ?: ($stored ?: 'ماژول');
                };

                $tab2Modules = collect($tab2Modules)
                    ->map(static function ($item) use ($resolveModuleLabel) {
                        $type = is_array($item) ? ($item['type'] ?? 'module') : 'module';

                        return [
                            'title' => $resolveModuleLabel($item),
                            'subtitle' => $type === 'feature' ? 'قابلیت فعال در اشتراک' : 'ماژول فعال در اشتراک',
                        ];
                    })
                    ->values()
                    ->all();
            @endphp
            <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-black text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ماژول‌ها و دسترسی‌های فعال این پکیج و سفارش</span>
                    </h4>
                    <span
                        class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                {{ reseller_fa_num(count($tab2Modules)) }} ماژول فعال
                            </span>
                </div>

                @if(!empty($tab2Modules))
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($tab2Modules as $item)
                            <div
                                class="p-3.5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 flex items-center gap-3 shadow-2xs">
                                <span
                                    class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <span
                                        class="text-xs font-black text-gray-900 dark:text-white block truncate">{{ $item['title'] }}</span>
                                    <span
                                        class="text-xs text-gray-400 block truncate">{{ $item['subtitle'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-900/40 text-center text-xs text-gray-400">
                        اطلاعات ماژول‌های فعال برای این سفارش ذخیره نشده است.
                    </div>
                @endif
            </div>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card>
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-amber-50/70 to-transparent dark:from-amber-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="p-2.5 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl shadow-md shadow-amber-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">ارتقای آنلاین پکیج
                        و صدور فاکتور مابه‌التفاوت</h3>
                    <p class="text-xs text-gray-400 mt-0.5">پکیج بالاتر را انتخاب کنید تا مابه‌التفاوت به صورت
                        زنده محاسبه و فاکتور ارتقا صادر شود</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            <form method="POST" action="{{ route('reseller.orders.upgrade-package', $order->id) }}"
                  @submit="upgradeSubmitting = true" class="space-y-8">
                @csrf
                <input type="hidden" name="new_package_id" :value="selectedUpgradePackageId">
                <input type="hidden" name="calculation_mode" :value="upgradeCalcMode">
                <input type="hidden" name="custom_price"
                       :value="upgradeCalcMode === 'custom' ? customUpgradePrice.replace(/\D/g, '') : ''">
                <input type="hidden" name="issue_invoice" :value="upgradeIssueInvoice ? '1' : '0'">

                {{-- Step 1: Package Selection Grid --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                            ۱. پکیج مقصد برای ارتقا را انتخاب کنید:
                        </label>
                        <span class="text-xs text-gray-400">قیمت‌ها بر اساس دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }} نمایش داده شده‌اند</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="pkg in packagesData" :key="pkg.id">
                            <div @click="canSelectPackage(pkg) && selectUpgradePackage(pkg.id)"
                                 class="p-5 rounded-2xl border-2 transition-all text-right flex flex-col justify-between relative overflow-hidden"
                                 :class="[
                                             !canSelectPackage(pkg)
                                                 ? 'opacity-60 cursor-not-allowed bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700/60'
                                                 : selectedUpgradePackageId == pkg.id
                                                     ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 ring-4 ring-amber-500/20 cursor-pointer shadow-md'
                                                     : 'border-gray-200 dark:border-gray-700/70 hover:border-amber-400 bg-white dark:bg-gray-800/60 cursor-pointer hover:shadow-sm'
                                         ]">
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-2.5">
                                                    <span class="w-3.5 h-3.5 rounded-full shrink-0"
                                                          :style="`background-color: ${pkg.color || '#6366f1'}`"></span>
                                            <h5 class="font-black text-sm sm:text-base text-gray-900 dark:text-white"
                                                x-text="pkg.name"></h5>
                                        </div>
                                        <template x-if="pkg.id == currentPackageId">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 bg-gray-200 dark:bg-gray-700 px-2.5 py-0.5 rounded-full">پکیج فعلی</span>
                                        </template>
                                        <template x-if="pkg.id != currentPackageId && isPackageCheaper(pkg)">
                                                    <span
                                                        class="text-xs font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-800/50 px-2.5 py-0.5 rounded-full">تعرفه کمتر</span>
                                        </template>
                                        <template
                                            x-if="pkg.id != currentPackageId && !isPackageCheaper(pkg) && selectedUpgradePackageId == pkg.id">
                                                    <span
                                                        class="text-xs font-black text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/60 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                       stroke-linejoin="round"
                                                                                       stroke-width="2.5"
                                                                                       d="M5 13l4 4L19 7"/></svg>
                                                        انتخاب شده
                                                    </span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2"
                                       x-text="pkg.description || 'پکیج استاندارد سامانه CRM'"></p>
                                </div>

                                <div
                                    class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                            <span class="text-gray-400 font-bold"
                                                  x-text="toFa(pkg.modules_count) + ' ماژول پیش‌فرض'"></span>
                                    <div class="font-black text-gray-900 dark:text-white tabular-nums dir-ltr">
                                        <span x-text="toFa(formatNumber(pkg.price_for_order_cycle))"></span>
                                        <span
                                            class="text-xs text-gray-400 font-normal ms-1">{{ $currencyLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Step 2: Calculation Mode Selection --}}
                <div x-show="selectedUpgradePackageId" x-cloak
                     class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <label class="block text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                        ۲. شیوه محاسبه مابه‌التفاوت:
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label
                            class="flex items-start gap-3 p-4 rounded-2xl border-2 text-xs cursor-pointer transition"
                            :class="upgradeCalcMode === 'prorated' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="prorated" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-4 h-4 rounded-full border border-gray-400 flex items-center justify-center shrink-0 mt-0.5"
                                :class="upgradeCalcMode === 'prorated' ? 'border-amber-600 bg-amber-600 ring-2 ring-amber-500/20' : ''"></span>
                            <div>
                                        <span
                                            class="font-black block text-xs sm:text-sm">تسهیم به نسبت (Prorated)</span>
                                <span class="text-xs text-gray-400 block mt-1">محاسبه دقیق بر پایه تعداد روزهای باقیمانده تا پایان لایسنس</span>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 rounded-2xl border-2 text-xs cursor-pointer transition"
                            :class="upgradeCalcMode === 'full' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="full" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-4 h-4 rounded-full border border-gray-400 flex items-center justify-center shrink-0 mt-0.5"
                                :class="upgradeCalcMode === 'full' ? 'border-amber-600 bg-amber-600 ring-2 ring-amber-500/20' : ''"></span>
                            <div>
                                <span class="font-black block text-xs sm:text-sm">مابه‌التفاوت کامل دوره</span>
                                <span class="text-xs text-gray-400 block mt-1">کسر کامل مبلغ پکیج قبلی از پکیج جدید برای کل دوره</span>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 rounded-2xl border-2 text-xs cursor-pointer transition"
                            :class="upgradeCalcMode === 'custom' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="custom" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-4 h-4 rounded-full border border-gray-400 flex items-center justify-center shrink-0 mt-0.5"
                                :class="upgradeCalcMode === 'custom' ? 'border-amber-600 bg-amber-600 ring-2 ring-amber-500/20' : ''"></span>
                            <div>
                                <span class="font-black block text-xs sm:text-sm">مبلغ توافقی / دلخواه</span>
                                <span class="text-xs text-gray-400 block mt-1">تعیین مستقیم مبلغ فاکتور ارتقا توسط خود شما</span>
                            </div>
                        </label>
                    </div>

                    <div x-show="upgradeCalcMode === 'custom'" class="pt-2">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1.5">مبلغ مورد
                            نظر مابه‌التفاوت ({{ $currencyLabel }}):</label>
                        <input type="text" x-model="customUpgradePrice" @input="formatCustomPrice($event)"
                               placeholder="مثال: ۵۰۰,۰۰۰"
                               class="w-full sm:w-80 text-sm rounded-xl border border-gray-200 bg-white dark:bg-gray-900 px-4 py-2.5 font-bold dir-ltr text-right focus:border-amber-500 focus:ring-amber-500/20">
                    </div>
                </div>

                {{-- Step 3: LIVE CALCULATION BREAKDOWN CARD --}}
                <div x-show="selectedUpgradePackageId" x-cloak
                     class="p-6 rounded-3xl bg-amber-50/50 dark:bg-amber-950/20 border-2 border-amber-200 dark:border-amber-800/50 space-y-4">
                    <div
                        class="flex items-center justify-between pb-3 border-b border-amber-200/70 dark:border-amber-800/50">
                        <h4 class="text-sm font-black text-amber-900 dark:text-amber-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span>محاسبه زنده مابه‌التفاوت ارتقای پکیج</span>
                        </h4>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300"
                              x-text="'ارتقا به «' + (selectedNewPackage ? selectedNewPackage.name : '') + '»'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div
                            class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-amber-100 dark:border-amber-900/40">
                            <span class="text-gray-400 block mb-1">قیمت دوره پکیج جدید:</span>
                            <span class="font-black text-sm text-gray-900 dark:text-white tabular-nums dir-ltr"
                                  x-text="toFa(formatNumber(newPackagePrice)) + ' {{ $currencyLabel }}'"></span>
                        </div>

                        <div
                            class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-amber-100 dark:border-amber-900/40">
                            <span class="text-gray-400 block mb-1">کسر بابت ارزش پکیج فعلی:</span>
                            <span class="font-black text-sm text-gray-900 dark:text-white tabular-nums dir-ltr"
                                  x-text="toFa(formatNumber(oldPackagePrice)) + ' {{ $currencyLabel }}'"></span>
                        </div>

                        <div
                            class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-amber-100 dark:border-amber-900/40">
                            <span class="text-gray-400 block mb-1">روزهای باقیمانده لایسنس:</span>
                            <span
                                class="font-black text-sm text-amber-700 dark:text-amber-300 tabular-nums dir-ltr"
                                x-text="toFa(remainingDaysCount) + ' از ' + toFa(totalCycleDaysCount) + ' روز'"></span>
                        </div>

                        <div
                            class="p-3.5 bg-amber-100/70 dark:bg-amber-900/40 rounded-2xl border border-amber-300 dark:border-amber-700">
                                    <span class="text-amber-900 dark:text-amber-200 font-bold block mb-1"
                                          x-text="upgradeIssueInvoice ? 'مبلغ نهایی فاکتور ارتقا:' : 'مابه‌التفاوت محاسبه‌شده:'"></span>
                            <span
                                class="font-black text-base text-amber-700 dark:text-amber-300 tabular-nums dir-ltr"
                                x-text="finalUpgradeAmountFormatted + ' {{ $currencyLabel }}'"></span>
                        </div>
                    </div>

                    {{-- Additional modules note --}}
                    <template x-if="newModulesAdded.length > 0">
                        <div
                            class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40 flex items-center gap-2.5 text-xs text-emerald-800 dark:text-emerald-300">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>ماژول‌های جدیدی که به سامانه اضافه خواهند شد: <strong class="font-black"
                                                                                        x-text="newModulesAdded.join('، ')"></strong></span>
                        </div>
                    </template>
                </div>

                {{-- Step 4: Invoice Toggle, Notes and Submit --}}
                <div x-show="selectedUpgradePackageId" x-cloak class="space-y-4">
                    {{-- Issue Invoice Switch Card --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 block">صدور فاکتور برای ارتقای پکیج</span>
                            <p class="text-xs text-gray-400 mt-0.5">در صورت غیرفعال بودن، ارتقای پکیج
                                مستقیماً روی سفارش و سامانه اعمال شده و هیچ فاکتوری صادر نمی‌شود.</p>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                                    <span class="text-xs sm:text-sm font-bold transition-colors"
                                          :class="upgradeIssueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                                          x-text="upgradeIssueInvoice ? 'فاکتور صادر شود' : 'عدم صدور'"></span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" x-model="upgradeIssueInvoice" class="sr-only peer">
                                <div
                                    class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                                <div
                                    class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                    <svg
                                        class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5"
                               x-text="upgradeIssueInvoice ? 'یادداشت برای فاکتور ارتقا (اختیاری):' : 'یادداشت ارتقای پکیج (اختیاری):'">
                        </label>
                        <input type="text" name="notes" x-model="upgradeNotes"
                               placeholder="توضیحات تکمیلی پیرامون ارتقا..."
                               class="w-full text-xs sm:text-sm rounded-xl border border-gray-200 bg-white dark:bg-gray-800 px-4 py-3 text-gray-800 dark:text-gray-200 focus:border-amber-500">
                    </div>

                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="text-xs text-gray-500"
                             x-text="upgradeIssueInvoice ? 'با کلیک بر روی دکمه زیر، فاکتور ارتقای پکیج به صورت خودکار صادر شده و در وضعیت «در انتظار پرداخت» قرار خواهد گرفت.' : 'ارتقا بدون صدور فاکتور مالی انجام شده و پکیج جدید مستقیماً روی سفارش و سامانه مشتری اعمال خواهد شد.'">
                        </div>

                        <button type="submit"
                                :disabled="!selectedUpgradePackageId || !canSelectPackage(selectedNewPackage) || upgradeSubmitting"
                                class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 active:scale-95 text-white font-black text-sm shadow-lg shadow-amber-500/25 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                            <svg x-show="upgradeSubmitting" class="w-4 h-4 animate-spin" fill="none"
                                 viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span
                                x-text="upgradeIssueInvoice ? 'ارتقای پکیج و صدور فاکتور مابه‌التفاوت' : 'تایید و اعمال مستقیم ارتقای پکیج'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-resellerhub::card>
</div>
