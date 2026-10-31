@php
    use Modules\DomainManager\Entities\DomainRecord;
    use Modules\DomainManager\Entities\DomainReseller;

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    // 1. Direct linkage by service_order_id
    $domainRecord = DomainRecord::where('service_order_id', $order->id)->with(['reseller', 'client'])->first();

    // 2. Fallback: Auto-detect domain from order custom fields or notes if not explicitly linked
    $detectedDomain = '';
    if (!$domainRecord) {
        if (isset($displayCustomFields) && is_array($displayCustomFields)) {
            foreach ($displayCustomFields as $f) {
                $label = mb_strtolower($f['label'] ?? '');
                if (str_contains($label, 'دامن') || str_contains($label, 'domain')) {
                    $rawVal = trim((string)($f['value'] ?? ''));
                    $clean = preg_replace('/^https?:\/\//i', '', $rawVal);
                    $clean = preg_replace('/^www\./i', '', $clean);
                    $clean = trim(explode('/', $clean)[0]);
                    if (filter_var($clean, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || str_contains($clean, '.')) {
                        $detectedDomain = $clean;
                        break;
                    }
                }
            }
        }

        if (!$detectedDomain && !empty($order->notes)) {
            if (preg_match('/([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.(?:ir|com|net|org|co|info|biz|io|xyz|online|shop|me|app|site|store|tech))/i', $order->notes, $matches)) {
                $detectedDomain = $matches[1];
            }
        }

        if ($detectedDomain) {
            $matchedByDomain = DomainRecord::where('domain_name', $detectedDomain)->with(['reseller', 'client'])->first();
            if ($matchedByDomain && (empty($matchedByDomain->service_order_id) || $matchedByDomain->service_order_id == $order->id)) {
                $domainRecord = $matchedByDomain;
            }
        }
    }

    $isDomainCategory = ($order->service?->category?->slug === 'domain') || !empty($domainRecord);

    $resellers = DomainReseller::where('is_active', true)->get();
    $resellersListJs = $resellers->map(function ($r) {
        return [
            'id' => (string)$r->id,
            'name' => $r->name,
            'balance' => number_format($r->balance ?? 0) . ' USD',
        ];
    })->values();

    $allExistingDomains = DomainRecord::orderBy('domain_name')->get(['id', 'domain_name', 'status', 'domain_reseller_id', 'service_order_id']);
    $existingDomainsJs = $allExistingDomains->map(function ($d) {
        return [
            'id' => (string)$d->id,
            'domain_name' => $d->domain_name,
            'status' => $d->status,
            'is_linked_other' => !empty($d->service_order_id),
            'order_id' => $d->service_order_id
        ];
    })->values();
@endphp

@if($isDomainCategory)
<div x-data="orderDomainManager()"
     class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden transition-all duration-300">

    {{-- Header matching Service Info Header --}}
    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
            <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path
                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </div>
            <span>سرویس و مشخصات دامنه</span>
        </h3>

        @if($domainRecord)
            @php
                $st = strtolower($domainRecord->status ?? 'active');
            @endphp
            @if($st === 'active')
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    فعال (Active)
                </span>
            @elseif($st === 'expired')
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    منقضی شده
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    در انتظار (Pending)
                </span>
            @endif
        @else
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                ثبت نشده
            </span>
        @endif
    </div>

    {{-- Body Content --}}
    <div class="p-6 space-y-6">
        @if($domainRecord)
            {{-- Domain is Registered and Linked --}}
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Domain Name --}}
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-xs font-bold text-gray-400">نام دامنه اصلی</span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-black text-gray-900 dark:text-white text-base block select-all" dir="ltr">
                                {{ $domainRecord->domain_name }}
                            </span>
                            <a href="http://{{ $domainRecord->domain_name }}" target="_blank" rel="noopener"
                               class="p-1 rounded-lg text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                               title="مشاهده وب‌سایت">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Reseller --}}
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-xs font-bold text-gray-400">کارگزار ثبت‌کننده</span>
                        <span class="font-black text-gray-900 dark:text-white text-sm truncate block">
                            {{ $domainRecord->reseller->name ?? 'کارگزار متصل' }}
                        </span>
                    </div>

                    {{-- Registration Date --}}
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-xs font-bold text-gray-400">تاریخ ثبت دامنه</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm block">
                            {{ $domainRecord->creation_date ? $faNum(jdate($domainRecord->creation_date)->format('Y/m/d')) : '—' }}
                        </span>
                    </div>

                    {{-- Expiry Date --}}
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-xs font-bold text-gray-400">تاریخ انقضای دامنه</span>
                        @if($domainRecord->expiry_date)
                            @php
                                $isNearExpiry = now()->diffInDays($domainRecord->expiry_date, false) <= 30;
                            @endphp
                            <span
                                class="font-bold text-sm block {{ $isNearExpiry ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-gray-800 dark:text-gray-200' }}">
                                {{ $faNum(jdate($domainRecord->expiry_date)->format('Y/m/d')) }}
                            </span>
                        @else
                            <span class="font-bold text-gray-400 text-sm block">—</span>
                        @endif
                    </div>
                </div>

                {{-- Nameservers Summary --}}
                @php
                    $nsList = is_array($domainRecord->nameservers) ? $domainRecord->nameservers : json_decode($domainRecord->nameservers ?? '[]', true);
                @endphp
                @if(!empty($nsList) && is_array($nsList))
                    <div
                        class="bg-gray-50/60 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-800/80 space-y-2">
                        <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">دی‌ان‌اس‌های فعال (Nameservers)</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($nsList as $ns)
                                @if(!empty($ns))
                                    <span
                                        class="px-2.5 py-1 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-gray-200/80 dark:border-gray-700 text-xs font-bold select-all"
                                        dir="ltr">
                                        {{ $ns }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Security & WHOIS badges --}}
                @if($domainRecord->privacy_protected)
                    <div class="flex flex-wrap items-center gap-2.5 pt-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 text-xs font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            مخفی‌سازی هویت WHOIS فعال است
                        </span>
                    </div>
                @endif

                {{-- Quick Actions Bar --}}
                <div
                    class="pt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700/50">
                    <span
                        class="text-xs text-gray-400">اطلاعات این دامنه به صورت برخط با کارگزاری ثبت‌کننده متصل است.</span>

                    <div class="flex items-center gap-2">
                        @can('domainmanager.domains.manage')
                            <button type="button" @click="unlinkDomain('{{ $domainRecord->domain_name }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-rose-950/30 dark:hover:text-rose-400 text-xs font-bold transition-colors cursor-pointer"
                                    title="قطع اتصال دامنه از این سفارش">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>حذف اتصال</span>
                            </button>
                        @endcan

                        @can('domainmanager.domains.view')
                            <a href="{{ route('user.domainmanager.domains.show', $domainRecord->domain_name) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
                                <span>مدیریت کامل دامنه</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State: No domain created/attached yet --}}
            <div class="text-center py-4 space-y-4">
                <div
                    class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">دامنه‌ای برای این سفارش ثبت یا متصل
                        نشده است</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
                        می‌توانید مستقیماً از این بخش دامنه جدید را در کارگزاری ثبت کرده یا دامنه از قبل ثبت‌شده را به
                        این سفارش متصل نمایید.
                    </p>
                </div>

                <div>
                    <button type="button" @click="openModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all duration-200 active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ثبت یا اتصال دامنه</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal for Domain Provisioning / Linkage --}}
    @if(!$domainRecord)
        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                     @click="!submitting && (openModal = false)"></div>

                <div
                    class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-start">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path
                                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white">ثبت یا اتصال دامنه به
                                    سفارش</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">برای سفارش
                                    شماره {{ $order->order_number }}</p>
                            </div>
                        </div>
                        <button type="button" @click="openModal = false" :disabled="submitting"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Tabs --}}
                    <div class="flex items-center gap-2 p-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-2xl">
                        <button type="button" @click="activeModalTab = 'register'"
                                :class="activeModalTab === 'register' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-bold'"
                                class="flex-1 py-2 rounded-xl text-xs transition-all cursor-pointer">
                            ثبت آنلاین دامنه جدید
                        </button>
                        <button type="button" @click="activeModalTab = 'link'"
                                :class="activeModalTab === 'link' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-bold'"
                                class="flex-1 py-2 rounded-xl text-xs transition-all cursor-pointer">
                            اتصال به دامنه موجود در سیستم
                        </button>
                        <button type="button" @click="activeModalTab = 'transfer'"
                                :class="activeModalTab === 'transfer' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-bold'"
                                class="flex-1 py-2 rounded-xl text-xs transition-all cursor-pointer">
                            انتقال دامنه (Transfer)
                        </button>
                    </div>

                    {{-- TAB 1: Live Register --}}
                    <div x-show="activeModalTab === 'register'" class="space-y-4">
                        <form @submit.prevent="submitRegisterDomain()" class="space-y-4">

                            {{-- Reseller Dropdown --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    کارگزار ثبت‌کننده <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative" :class="{ 'z-50': dropdowns.resellerRegOpen }">
                                    <button type="button"
                                            @click="dropdowns.resellerRegOpen = !dropdowns.resellerRegOpen"
                                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-white flex items-center justify-between cursor-pointer">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span x-text="getSelectedResellerTitle(registerForm.reseller_id)"
                                                  class="truncate"></span>
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                            :class="{ 'rotate-180': dropdowns.resellerRegOpen }" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="dropdowns.resellerRegOpen"
                                         @click.outside="dropdowns.resellerRegOpen = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <template x-for="r in resellers" :key="r.id">
                                            <div
                                                @click="registerForm.reseller_id = r.id; dropdowns.resellerRegOpen = false"
                                                class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(registerForm.reseller_id) === String(r.id) }">
                                                <span x-text="r.name" class="font-bold"></span>
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono"
                                                    x-text="'موجودی: ' + r.balance"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Domain Name & Availability Check --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    نام دامنه کامل <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="registerForm.domain_name" required
                                           placeholder="example.com" dir="ltr"
                                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:focus:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                    <button type="button" @click="checkAvailability()"
                                            :disabled="checkingAvail || !registerForm.domain_name"
                                            class="px-5 py-3 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 text-xs font-bold transition-all shrink-0 disabled:opacity-50 cursor-pointer shadow-xs active:scale-95">
                                        <span x-text="checkingAvail ? 'در حال استعلام...' : 'استعلام آزاد بودن'"></span>
                                    </button>
                                </div>
                                <template x-if="availStatus">
                                    <div
                                        class="mt-2 text-xs font-bold flex items-center gap-1.5 p-2.5 rounded-xl border"
                                        :class="availStatus === 'available' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20'">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2.5">
                                            <path x-show="availStatus === 'available'" stroke-linecap="round"
                                                  stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            <path x-show="availStatus !== 'available'" stroke-linecap="round"
                                                  stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span
                                            x-text="availStatus === 'available' ? 'دامنه آزاد است و امکان ثبت آنلاین در کارگزار وجود دارد.' : 'دامنه قبلاً ثبت شده یا توسط رجیسترار مسدود است.'"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Duration & IRNIC Handle --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Duration Dropdown --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        مدت دوره ثبت <span class="text-rose-500 font-black">*</span>
                                    </label>
                                    <div class="relative" :class="{ 'z-50': dropdowns.yearsRegOpen }">
                                        <button type="button" @click="dropdowns.yearsRegOpen = !dropdowns.yearsRegOpen"
                                                class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-white flex items-center justify-between cursor-pointer outline-none">
                                            <span x-text="getSelectedDurationTitle(registerForm.years)"></span>
                                            <svg
                                                class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                                :class="{ 'rotate-180': dropdowns.yearsRegOpen }" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="dropdowns.yearsRegOpen"
                                             @click.outside="dropdowns.yearsRegOpen = false" x-cloak
                                             class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                            <template x-for="d in durationOptions" :key="d.value">
                                                <div
                                                    @click="registerForm.years = d.value; dropdowns.yearsRegOpen = false"
                                                    class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(registerForm.years) === String(d.value) }">
                                                    <span x-text="d.title" class="font-bold"></span>
                                                    <span x-show="d.badge"
                                                          class="text-[10px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
                                                          x-text="d.badge"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- If .ir is selected/typed, show IRNIC Handle --}}
                                <div x-show="registerForm.domain_name.endsWith('.ir')" class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">شناسه ایرنیک
                                        (IRNIC Handle) <span class="text-rose-500 font-black">*</span></label>
                                    <input type="text" x-model="registerForm.holder_handle" placeholder="ab123-irnic"
                                           dir="ltr"
                                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:focus:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                </div>
                            </div>

                            {{-- Optional Manual WHOIS Contacts (For International Domains - strictly explicit, never auto-pulling) --}}
                            <div x-show="!registerForm.domain_name.endsWith('.ir')"
                                 class="p-4 rounded-2xl bg-gray-50/90 dark:bg-gray-800/40 border border-gray-200/80 dark:border-gray-700 space-y-3">
                                <span class="block text-xs font-black text-gray-700 dark:text-gray-300">مشخصات اختصاصی مالک دامنه بین‌المللی (اختیاری):</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">نام
                                            کامل انگلیسی مالک</label>
                                        <input type="text" x-model="registerForm.contact_name" placeholder="John Doe"
                                               dir="ltr"
                                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">ایمیل
                                            اختصاصی مالک</label>
                                        <input type="email" x-model="registerForm.contact_email"
                                               placeholder="owner@domain.com" dir="ltr"
                                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                    </div>
                                </div>
                            </div>

                            {{-- Nameservers --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">DNS
                                        1</label>
                                    <input type="text" x-model="registerForm.ns1" placeholder="ns1.domain.com" dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1">DNS
                                        2</label>
                                    <input type="text" x-model="registerForm.ns2" placeholder="ns2.domain.com" dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="openModal = false" :disabled="submitting"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                    انصراف
                                </button>
                                <button type="submit" :disabled="submitting"
                                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 cursor-pointer">
                                    <template x-if="submitting">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span
                                        x-text="submitting ? 'در حال ارسال درخواست به کارگزار...' : 'تایید و ثبت آنلاین دامنه'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 2: Link Existing Domain in System --}}
                    <div x-show="activeModalTab === 'link'" class="space-y-4">
                        <form @submit.prevent="submitLinkExistingDomain()" class="space-y-4">
                            <div
                                class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 text-xs text-indigo-800 dark:text-indigo-300 leading-relaxed font-bold">
                                اگر این دامنه قبلاً در کارگزاری یا سیستم ثبت شده است، می‌توانید آن را انتخاب کنید تا
                                مستقیماً و بدون صرف هزینه به این سفارش متصل گردد.
                            </div>

                            {{-- Searchable Existing Domain Dropdown --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    انتخاب دامنه از دامنه‌های ثبت‌شده <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative" :class="{ 'z-50': dropdowns.linkDomainOpen }">
                                    <button type="button" @click="dropdowns.linkDomainOpen = !dropdowns.linkDomainOpen"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-white flex items-center justify-between cursor-pointer outline-none">
                                        <span x-text="linkForm.domain_name || 'انتخاب دامنه از لیست...'"
                                              class="truncate font-bold" dir="ltr"></span>
                                        <svg
                                            class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                            :class="{ 'rotate-180': dropdowns.linkDomainOpen }" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="dropdowns.linkDomainOpen"
                                         @click.outside="dropdowns.linkDomainOpen = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2.5 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1.5">
                                            <input type="text" x-model="searchExisting"
                                                   placeholder="جستجوی نام دامنه..."
                                                   class="w-full text-xs p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 placeholder:font-normal focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none font-bold">
                                        </div>

                                        <template x-for="d in filteredExistingDomains" :key="d.id">
                                            <div
                                                @click="linkForm.domain_name = d.domain_name; dropdowns.linkDomainOpen = false"
                                                class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': linkForm.domain_name === d.domain_name }">
                                                <div class="flex items-center gap-2 truncate">
                                                    <span class="w-2 h-2 rounded-full"
                                                          :class="d.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                                    <span x-text="d.domain_name" class="font-bold truncate"
                                                          dir="ltr"></span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <template x-if="d.is_linked_other">
                                                        <span
                                                            class="text-[10px] px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 font-bold"
                                                            x-text="'سفارش #' + d.order_id"></span>
                                                    </template>
                                                    <span
                                                        class="text-[10px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold"
                                                        x-text="d.status"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="filteredExistingDomains.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center font-bold">
                                            دامنه‌ای یافت نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="openModal = false" :disabled="submitting"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                    انصراف
                                </button>
                                <button type="submit" :disabled="submitting || !linkForm.domain_name"
                                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/25 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 cursor-pointer">
                                    <span x-text="submitting ? 'در حال اتصال...' : 'اتصال دامنه به این سفارش'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 3: Transfer Domain --}}
                    <div x-show="activeModalTab === 'transfer'" class="space-y-4">
                        <form @submit.prevent="submitTransferDomain()" class="space-y-4">

                            {{-- Reseller Dropdown (Transfer) --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    کارگزار مقصد انتقال <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative" :class="{ 'z-50': dropdowns.resellerTransOpen }">
                                    <button type="button"
                                            @click="dropdowns.resellerTransOpen = !dropdowns.resellerTransOpen"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-white flex items-center justify-between cursor-pointer outline-none">
                                        <span x-text="getSelectedResellerTitle(transferForm.reseller_id)"></span>
                                        <svg
                                            class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                            :class="{ 'rotate-180': dropdowns.resellerTransOpen }" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="dropdowns.resellerTransOpen"
                                         @click.outside="dropdowns.resellerTransOpen = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <template x-for="r in resellers" :key="r.id">
                                            <div
                                                @click="transferForm.reseller_id = r.id; dropdowns.resellerTransOpen = false"
                                                class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(transferForm.reseller_id) === String(r.id) }">
                                                <span x-text="r.name" class="font-bold"></span>
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono"
                                                    x-text="'موجودی: ' + r.balance"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">نام دامنه
                                        جهت انتقال <span class="text-rose-500 font-black">*</span></label>
                                    <input type="text" x-model="transferForm.domain_name" required
                                           placeholder="example.com" dir="ltr"
                                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:focus:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">کد انتقال
                                        (EPP Code) <span class="text-rose-500 font-black">*</span></label>
                                    <input type="text" x-model="transferForm.auth_code" required
                                           placeholder="Auth / EPP Code" dir="ltr"
                                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 focus:bg-white px-4 py-3 text-sm font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-700 dark:bg-gray-900/60 dark:focus:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 transition-all outline-none">
                                </div>
                            </div>

                            <div
                                class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="openModal = false" :disabled="submitting"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                    انصراف
                                </button>
                                <button type="submit" :disabled="submitting"
                                        class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs sm:text-sm shadow-md shadow-amber-500/25 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 cursor-pointer">
                                    <span
                                        x-text="submitting ? 'در حال ارسال درخواست انتقال...' : 'ارسال درخواست انتقال دامنه'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    window.orderDomainManager = function () {
        return {
            openModal: false,
            activeModalTab: 'register',
            submitting: false,
            checkingAvail: false,
            availStatus: null,
            searchExisting: '',
            dropdowns: {
                resellerRegOpen: false,
                yearsRegOpen: false,
                linkDomainOpen: false,
                resellerTransOpen: false,
            },
            resellers: {!! json_encode($resellersListJs, JSON_UNESCAPED_UNICODE) !!},
            existingDomains: {!! json_encode($existingDomainsJs, JSON_UNESCAPED_UNICODE) !!},
            durationOptions: [
                {value: 1, title: '۱ سال ثبت دامنه', badge: 'دوره پایه'},
                {value: 2, title: '۲ سال ثبت دامنه', badge: 'پیشنهادی'},
                {value: 3, title: '۳ سال ثبت دامنه', badge: '۳۶ ماهه'},
                {value: 5, title: '۵ سال ثبت دامنه', badge: 'اقتصادی ۵ ساله'},
                {value: 10, title: '۱۰ سال ثبت دامنه', badge: 'حداکثر بازه'},
            ],
            registerForm: {
                order_id: '{{ $order->id }}',
                reseller_id: '{{ $resellers->first()?->id ?? "" }}',
                domain_name: '{{ $detectedDomain ?? "" }}',
                years: 1,
                action_type: 'register',
                holder_handle: '',
                contact_name: '',
                contact_email: '',
                ns1: 'ns1.reseller.world',
                ns2: 'ns2.reseller.world'
            },
            linkForm: {
                order_id: '{{ $order->id }}',
                domain_name: '{{ $detectedDomain ?? "" }}'
            },
            transferForm: {
                order_id: '{{ $order->id }}',
                reseller_id: '{{ $resellers->first()?->id ?? "" }}',
                domain_name: '{{ $detectedDomain ?? "" }}',
                auth_code: '',
                action_type: 'transfer',
                years: 1
            },
            get filteredExistingDomains() {
                if (!this.searchExisting.trim()) return this.existingDomains;
                const q = this.searchExisting.toLowerCase().trim();
                return this.existingDomains.filter(d => d.domain_name.toLowerCase().includes(q));
            },
            getSelectedResellerTitle(id) {
                const found = this.resellers.find(r => String(r.id) === String(id));
                if (!found) return 'انتخاب کارگزار...';
                return found.name + ' (' + found.balance + ')';
            },
            getSelectedDurationTitle(val) {
                const found = this.durationOptions.find(d => Number(d.value) === Number(val));
                if (!found) return val + ' سال';
                return found.title + (found.badge ? ' (' + found.badge + ')' : '');
            },
            async checkAvailability() {
                if (!this.registerForm.domain_name || this.checkingAvail) return;
                this.checkingAvail = true;
                this.availStatus = null;

                try {
                    const res = await fetch('{{ route("user.domainmanager.orders.check-availability") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            reseller_id: this.registerForm.reseller_id,
                            domain: this.registerForm.domain_name
                        })
                    });
                    const data = await res.json();
                    this.availStatus = data.available ? 'available' : 'unavailable';
                } catch (e) {
                    this.availStatus = null;
                } finally {
                    this.checkingAvail = false;
                }
            },
            async submitRegisterDomain() {
                if (this.submitting) return;
                this.submitting = true;

                try {
                    const res = await fetch('{{ route("user.domainmanager.orders.store-domain") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.registerForm)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message || 'دامنه با موفقیت ثبت و به سفارش متصل گردید.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'خطا در ثبت دامنه.');
                    }
                } catch (e) {
                    alert('خطا در برقراری ارتباط با سرور.');
                } finally {
                    this.submitting = false;
                }
            },
            async submitLinkExistingDomain() {
                if (this.submitting) return;
                this.submitting = true;

                try {
                    const res = await fetch('{{ route("user.domainmanager.orders.link-domain") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.linkForm)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message || 'دامنه با موفقیت به سفارش متصل گردید.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'خطا در اتصال دامنه.');
                    }
                } catch (e) {
                    alert('خطا در برقراری ارتباط با سرور.');
                } finally {
                    this.submitting = false;
                }
            },
            async submitTransferDomain() {
                if (this.submitting) return;
                this.submitting = true;

                try {
                    const res = await fetch('{{ route("user.domainmanager.orders.store-domain") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.transferForm)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message || 'درخواست انتقال با موفقیت ثبت شد.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'خطا در انتقال دامنه.');
                    }
                } catch (e) {
                    alert('خطا در برقراری ارتباط با سرور.');
                } finally {
                    this.submitting = false;
                }
            },
            async unlinkDomain(domainName) {
                if (!confirm(`آیا از قطع اتصال دامنه ${domainName} از این سفارش اطمینان دارید؟`)) return;

                try {
                    const res = await fetch('{{ route("user.domainmanager.orders.unlink-domain") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order_id: '{{ $order->id }}',
                            domain_name: domainName
                        })
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'خطا در حذف اتصال دامنه.');
                    }
                } catch (e) {
                    alert('خطا در برقراری ارتباط.');
                }
            }
        };
    }
</script>
@endif
