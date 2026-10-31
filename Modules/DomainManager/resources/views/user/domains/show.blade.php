@php use Modules\Clients\Entities\Client; @endphp
@extends('layouts.user')

@section('title', 'مدیریت دامنه: ' . $domain->domain_name)

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $allClientsList = Client::orderBy('full_name')->get();
    $customersListForJs = collect($allClientsList)->map(function ($c) {
        $displayName = trim((string)($c->full_name ?? ''));
        if (empty($displayName)) {
            $displayName = trim((string)($c->username ?? '')) ?: ('مشتری کد ' . $c->id);
        }
        return [
            'id'       => (string) $c->id,
            'name'     => $displayName,
            'email'    => (string) ($c->email ?? ''),
            'phone'    => (string) ($c->phone ?? ''),
            'username' => (string) ($c->username ?? ''),
        ];
    })->values();

    $ordersListForJs = collect($allOrdersList ?? [])->map(function ($o) {
        $clientName = $o->customer?->full_name ?? ($o->client_name ?? '—');
        $serviceName = $o->service?->name ?? 'سفارش خدمات';
        return [
            'id'           => (string) $o->id,
            'order_number' => (string) $o->order_number,
            'service_name' => $serviceName,
            'client_name'  => $clientName,
            'customer_id'  => (string) ($o->customer_id ?? ''),
        ];
    })->values();
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative"
     x-data="{
        activeTab: 'whois',
        toast: { show: false, message: '', type: 'success', timer: null },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 4000);
        }
     }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('domainmanager::partials.toast')

    {{-- Top Header Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4">
            <span
                class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path
                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        مدیریت دامنه
                    </h1>
                    <span
                        class="px-3.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 text-base font-black select-all"
                        dir="ltr">
                        {{ $domain->domain_name }}
                    </span>

                    {{-- Status Badge --}}
                    @php
                        $st = strtolower($domain->status ?? '');
                    @endphp
                    @if($st === 'active')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-2xs">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            فعال (Active)
                        </span>
                    @elseif($st === 'expired')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            منقضی شده (Expired)
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            در انتظار (Pending)
                        </span>
                    @endif
                </div>

                <div
                    class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        مشاهده وب‌سایت:
                        <a href="http://{{ $domain->domain_name }}" target="_blank" rel="noopener"
                           class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1"
                           dir="ltr">
                            {{ $domain->domain_name }}
                        </a>
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">•</span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        کارگزار:
                        <span
                            class="font-bold text-gray-800 dark:text-gray-200">{{ $domain->reseller->name ?? '—' }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Top Actions --}}
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('user.domainmanager.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                </svg>
                <span>پیشخوان دامنه</span>
            </a>

            <a href="{{ route('user.domainmanager.domains.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20 text-xs sm:text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>لیست دامنه‌ها</span>
            </a>

            <form method="POST" action="{{ route('user.domainmanager.domains.sync') }}" class="inline">
                @csrf
                <input type="hidden" name="domain_id" value="{{ $domain->id }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>همگام‌سازی از کارگزار</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('status'))
        <div
            class="p-5 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if(session('error') || $error)
        <div
            class="p-5 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ session('error') ?: $error }}
            </div>
        </div>
    @endif
    <div class="space-y-6">
        <div
            class="p-1.5 bg-gray-100/80 dark:bg-gray-900/50 rounded-2xl border border-gray-200/60 dark:border-gray-800 flex items-center gap-1.5 overflow-x-auto">
            <button @click="activeTab = 'whois'"
                    :class="activeTab === 'whois' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>مشخصات WHOIS مالک</span>
            </button>

            <button @click="activeTab = 'privacy'"
                    :class="activeTab === 'privacy' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>مخفی‌سازی WHOIS</span>
            </button>

            <button @click="activeTab = 'epp'"
                    :class="activeTab === 'epp' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <span>کد انتقال دامنه (EPP)</span>
            </button>

            <button @click="activeTab = 'renew'"
                    :class="activeTab === 'renew' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>تمدید دامنه</span>
            </button>

            <button @click="activeTab = 'child'"
                    :class="activeTab === 'child' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>کارگزارهای فرزند (Child)</span>
            </button>

            <button @click="activeTab = 'dnssec'"
                    :class="activeTab === 'dnssec' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>رکورد DNSSEC</span>
            </button>
        </div>

        {{-- Tab Panes Container --}}
        <div class="space-y-6">

            {{-- TAB 2: WHOIS CONTACT DETAILS --}}
            <div x-show="activeTab === 'whois'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <span>ویرایش اطلاعات تماس مالک دامنه (WHOIS Contacts)</span>
                        </h2>
                    </div>

                    @php
                        $registrant = $config['contacts']['holder'] ?? [];
                        $holderHandle = $config['holder'] ?? '';
                        $email = $registrant['email'] ?? '';
                        if (is_array($email)) {
                            $email = reset($email);
                        }
                    @endphp

                    <form method="POST"
                          action="{{ route('user.domainmanager.domains.modify-contacts', $domain->domain_name) }}"
                          class="p-6 space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">شناسه مالک دامنه (Holder Handle) <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="holder" required value="{{ old('holder', $holderHandle) }}"
                                       class="{{ $inputClass }} text-start font-sans" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">نام کامل مالک (انگلیسی) <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="name" required
                                       value="{{ old('name', $registrant['name'] ?? '') }}" class="{{ $inputClass }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">آدرس ایمیل <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="email" name="email" required value="{{ old('email', $email) }}"
                                       class="{{ $inputClass }} text-start font-sans" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">نام شرکت / سازمان</label>
                                <input type="text" name="organization"
                                       value="{{ old('organization', $registrant['organization'] ?? 'N/A') }}"
                                       class="{{ $inputClass }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">آدرس فیزیکی (انگلیسی) <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="address" required
                                       value="{{ old('address', $registrant['address'] ?? '') }}"
                                       class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">شماره تلفن همراه <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="phone" required
                                       value="{{ old('phone', $registrant['phone'] ?? '') }}"
                                       class="{{ $inputClass }} text-start font-sans" dir="ltr"
                                       placeholder="+98.9123456789">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">شهر <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="city" required
                                       value="{{ old('city', $registrant['city'] ?? '') }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">استان <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="state" required
                                       value="{{ old('state', $registrant['state'] ?? 'N/A') }}"
                                       class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">کد پستی <span class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="postCode" required
                                       value="{{ old('postCode', $registrant['postCode'] ?? '') }}"
                                       class="{{ $inputClass }} text-start font-sans" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">کد کشور (۲ حرفی) <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" name="country" required
                                       value="{{ old('country', $registrant['country'] ?? 'IR') }}"
                                       class="{{ $inputClass }} text-start uppercase font-sans" maxlength="2" dir="ltr">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex justify-end">
                            <button type="submit"
                                    class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95 flex items-center gap-2 cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>ذخیره مشخصات WHOIS</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 3: WHOIS PRIVACY --}}
            <div x-show="activeTab === 'privacy'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            <span>مخفی‌سازی مشخصات WHOIS (Privacy Protection)</span>
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            با فعال‌سازی این سرویس، اطلاعات تماس شخصی و ثبتی شما (مانند ایمیل، نام و شماره تلفن) در بانک
                            اطلاعاتی عمومی WHOIS پنهان شده و اطلاعات کارگزار نمایش داده می‌شود تا از ارسال ایمیل‌های
                            اسپم و سوءاستفاده‌های تبلیغاتی جلوگیری گردد.
                        </p>

                        <div
                            class="p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="font-black text-gray-900 dark:text-white text-sm block">وضعیت فعلی مخفی‌سازی</span>
                                <p class="text-xs text-gray-400 mt-0.5">در حال حاضر این امکان برای دامنه شما <strong
                                        class="{{ $domain->privacy_protected ? 'text-emerald-600' : 'text-gray-600' }}">{{ $domain->privacy_protected ? 'فعال' : 'غیرفعال' }}</strong>
                                    است.</p>
                            </div>

                            <form
                                action="{{ route('user.domainmanager.domains.toggle-privacy', $domain->domain_name) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="privacy"
                                       value="{{ $domain->privacy_protected ? '0' : '1' }}">
                                <button type="submit"
                                        class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer {{ $domain->privacy_protected ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white hover:shadow-emerald-500/30' }}">
                                    {{ $domain->privacy_protected ? 'غیرفعال کردن مخفی‌سازی' : 'فعال‌سازی مخفی‌سازی' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: EPP AUTH CODE --}}
            <div x-show="activeTab === 'epp'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </span>
                            <span>کد انتقال دامنه (EPP / Auth Code)</span>
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            جهت انتقال این دامنه به کارگزار دیگر، نیاز به کد محرمانه انتقال (EPP Code) دارید. با فشردن
                            دکمه زیر، کد استعلام شده و همزمان به آدرس ایمیل مالک دامنه ارسال خواهد شد.
                        </p>

                        <div
                            class="p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                            @if($domain->auth_code)
                                <div class="space-y-1">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">کد انتقال ثبت‌شده:</span>
                                    <span
                                        class="font-bold text-base text-indigo-600 dark:text-indigo-400 select-all tracking-wider block"
                                        dir="ltr">
                                        {{ $domain->auth_code }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $domain->auth_code }}'); showToast('کد انتقال کپی شد', 'success')"
                                            class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 text-xs font-bold transition-all shadow-2xs cursor-pointer">
                                        کپی کد
                                    </button>
                                    <form
                                        action="{{ route('user.domainmanager.domains.fetch-epp', $domain->domain_name) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer">
                                            درخواست مجدد کد
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">کد انتقال هنوز استعلام نشده است.</span>
                                <form action="{{ route('user.domainmanager.domains.fetch-epp', $domain->domain_name) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95 flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        <span>استعلام و دریافت کد EPP</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 5: RENEW DOMAIN --}}
            <div x-show="activeTab === 'renew'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-visible relative z-30">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span>تمدید آنلاین و آنی دامنه</span>
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('user.domainmanager.domains.renew', $domain->domain_name) }}"
                          class="p-6 space-y-6">
                        @csrf
                        <div class="max-w-md space-y-1.5"
                             x-data="{
                                open: false,
                                search: '',
                                selectedYears: '1',
                                options: [
                                    { value: '1', title: '۱ سال تمدید دامنه', subtitle: 'دوره پایه' },
                                    { value: '2', title: '۲ سال تمدید دامنه', subtitle: 'پیشنهادی' },
                                    { value: '3', title: '۳ سال تمدید دامنه', subtitle: '۳۶ ماهه' },
                                    { value: '4', title: '۴ سال تمدید دامنه', subtitle: '۴۸ ماهه' },
                                    { value: '5', title: '۵ سال تمدید دامنه', subtitle: 'اقتصادی ۵ ساله' },
                                    { value: '6', title: '۶ سال تمدید دامنه', subtitle: '۷۲ ماهه' },
                                    { value: '7', title: '۷ سال تمدید دامنه', subtitle: '۸۴ ماهه' },
                                    { value: '8', title: '۸ سال تمدید دامنه', subtitle: '۹۶ ماهه' },
                                    { value: '9', title: '۹ سال تمدید دامنه', subtitle: '۱۰۸ ماهه' },
                                    { value: '10', title: '۱۰ سال تمدید دامنه', subtitle: 'حداکثر بازه' }
                                ],
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        o.title.toLowerCase().includes(q) ||
                                        (o.subtitle && o.subtitle.toLowerCase().includes(q)) ||
                                        o.value.includes(q)
                                    );
                                },
                                select(opt) {
                                    this.selectedYears = opt ? String(opt.value) : '1';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.value) === String(this.selectedYears));
                                    if (!found) return 'انتخاب مدت تمدید...';
                                    return found.title + (found.subtitle ? ' (' + found.subtitle + ')' : '');
                                }
                             }">
                            <label class="{{ $labelClass }}">مدت زمان دوره تمدید <span class="text-rose-500 font-black">*</span></label>

                            <div class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="years" :value="selectedYears" required>

                                <button type="button" @click="open = !open"
                                        class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجوی دوره تمدید..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="opt in filteredOptions" :key="opt.value">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedYears) === String(opt.value) }">
                                            <span x-text="opt.title" class="truncate font-medium"></span>
                                            <span x-show="opt.subtitle"
                                                  class="text-[10px] px-1.5 py-0.5 rounded border border-gray-200/80 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shrink-0 font-medium"
                                                  x-text="opt.subtitle"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ گزینه‌ای پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex justify-end">
                            <button type="submit"
                                    class="px-7 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all duration-200 active:scale-95 flex items-center gap-2 cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>پرداخت و تمدید رسمی دامنه</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 6: CHILD HOSTS --}}
            <div x-show="activeTab === 'child'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span>دی‌ان‌اس‌های اختصاصی (Child Hosts / Glue Records)</span>
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        @if(empty($childHosts))
                            <div class="py-6 text-center text-xs text-gray-400 space-y-2">
                                <p>هیچ کارگزار فرزندی برای این دامنه ثبت نشده است.</p>
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-100 dark:border-gray-700/60 rounded-2xl">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                                    <thead
                                        class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 font-bold">
                                    <tr>
                                        <th class="px-4 py-3 text-start">نام دی‌ان‌اس اختصاصی (NS)</th>
                                        <th class="px-4 py-3 text-start">IP آدرس ست‌شده</th>
                                        <th class="px-4 py-3 text-end">عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($childHosts as $ch)
                                        <tr>
                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white"
                                                dir="ltr">{{ $ch['ns'] }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-600 dark:text-gray-300"
                                                dir="ltr">{{ $ch['ips'] }}</td>
                                            <td class="px-4 py-3 text-end">
                                                <form
                                                    action="{{ route('user.domainmanager.domains.child-hosts.delete', $domain->domain_name) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('آیا از حذف این دی‌ان‌اس مطمئن هستید؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="ns" value="{{ $ch['ns'] }}">
                                                    <button type="submit"
                                                            class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold transition-colors cursor-pointer">
                                                        حذف
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Add Child Form --}}
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 space-y-4">
                            <span class="block text-xs font-black text-gray-900 dark:text-white">ایجاد کارگزار فرزند جدید (Add Child NS)</span>
                            <form method="POST"
                                  action="{{ route('user.domainmanager.domains.child-hosts.add', $domain->domain_name) }}"
                                  class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                @csrf
                                <div>
                                    <label class="{{ $labelClass }}">نام کارگزار (NS)</label>
                                    <input type="text" name="ns" required placeholder="ns1.{{ $domain->domain_name }}"
                                           class="{{ $inputClass }} text-start" dir="ltr">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">IP سرور (IP Address)</label>
                                    <input type="text" name="ip" required placeholder="185.10.x.x"
                                           class="{{ $inputClass }} text-start" dir="ltr">
                                </div>
                                <button type="submit"
                                        class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-bold shadow-md transition-all active:scale-95 cursor-pointer">
                                    ثبت دی‌ان‌اس فرزند
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 7: DNSSEC --}}
            <div x-show="activeTab === 'dnssec'" class="space-y-6" x-transition style="display: none;">
                <div class="{{ $cardClass }} overflow-visible relative z-30">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <span>مدیریت امنیت پیشرفته DNSSEC</span>
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            پروتکل DNSSEC با امضای دیجیتال رکوردهای دی‌ان‌اس، از حملات جعل آدرس و مسموم‌سازی کش جلوگیری
                            می‌کند.
                        </p>

                        <div class="flex">
                            <form action="{{ route('user.domainmanager.domains.dnssec.delete', $domain->domain_name) }}"
                                  method="POST"
                                  onsubmit="return confirm('آیا از غیرفعال‌سازی و حذف تمام رکوردهای DNSSEC مطمئن هستید؟');"
                                  class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10 text-xs font-bold transition-all cursor-pointer">
                                    غیرفعال‌سازی و حذف کامل رکوردهای DNSSEC
                                </button>
                            </form>
                        </div>

                        {{-- Add Form --}}
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 space-y-4">
                            <span
                                class="block text-xs font-black text-gray-900 dark:text-white">افزودن کلید جدید DNSSEC</span>
                            <form method="POST"
                                  action="{{ route('user.domainmanager.domains.dnssec.add', $domain->domain_name) }}"
                                  class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="{{ $labelClass }}">برچسب کلید (Key Tag) <span
                                                class="text-rose-500 font-black">*</span></label>
                                        <input type="text" name="tag" required placeholder="مثال: 12345"
                                               class="{{ $inputClass }} text-start" dir="ltr">
                                    </div>

                                    {{-- Custom Alpine Dropdown for Algorithm --}}
                                    <div class="space-y-1.5"
                                         x-data="{
                                            open: false,
                                            search: '',
                                            selectedAlgo: '8',
                                            options: [
                                                { value: '1', title: 'RSA/MD5 (1)' },
                                                { value: '2', title: 'Diffie-Hellman (2)' },
                                                { value: '3', title: 'DSA/SHA1 (3)' },
                                                { value: '5', title: 'RSA/SHA-1 (5)' },
                                                { value: '7', title: 'RSASHA1-NSEC3-SHA1 (7)' },
                                                { value: '8', title: 'RSA/SHA-256 (8)', subtitle: 'پیشنهادی' },
                                                { value: '10', title: 'RSA/SHA-512 (10)' },
                                                { value: '13', title: 'ECDSA Curve P-256 with SHA-256 (13)', subtitle: 'مدرن و امن' },
                                                { value: '14', title: 'ECDSA Curve P-384 with SHA-384 (14)' }
                                            ],
                                            get filteredOptions() {
                                                if (!this.search.trim()) return this.options;
                                                const q = this.search.toLowerCase();
                                                return this.options.filter(o =>
                                                    o.title.toLowerCase().includes(q) ||
                                                    (o.subtitle && o.subtitle.toLowerCase().includes(q)) ||
                                                    o.value.includes(q)
                                                );
                                            },
                                            select(opt) {
                                                this.selectedAlgo = opt ? String(opt.value) : '8';
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedTitle() {
                                                let found = this.options.find(o => String(o.value) === String(this.selectedAlgo));
                                                if (!found) return 'انتخاب الگوریتم...';
                                                return found.title + (found.subtitle ? ' (' + found.subtitle + ')' : '');
                                            }
                                         }">
                                        <label class="{{ $labelClass }}">الگوریتم (Algorithm) <span
                                                class="text-rose-500 font-black">*</span></label>
                                        <div class="relative" :class="{ 'z-50': open }">
                                            <input type="hidden" name="algorithm" :value="selectedAlgo" required>

                                            <button type="button" @click="open = !open"
                                                    class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                                                <span x-text="getSelectedTitle()" class="truncate font-medium"
                                                      dir="ltr"></span>
                                                <svg
                                                    class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                                    :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[260px] sm:min-w-[320px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                                    <input type="text" x-model="search" placeholder="جستجوی الگوریتم..."
                                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                                </div>

                                                <template x-for="opt in filteredOptions" :key="opt.value">
                                                    <div @click="select(opt)"
                                                         class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedAlgo) === String(opt.value) }">
                                                        <span x-text="opt.title" class="truncate font-medium"
                                                              dir="ltr"></span>
                                                        <span x-show="opt.subtitle"
                                                              class="text-[10px] px-1.5 py-0.5 rounded border border-gray-200/80 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shrink-0 font-medium"
                                                              x-text="opt.subtitle"></span>
                                                    </div>
                                                </template>

                                                <div x-show="filteredOptions.length === 0"
                                                     class="p-3 text-xs text-gray-400 text-center">
                                                    هیچ گزینه‌ای پیدا نشد
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Custom Alpine Dropdown for Digest Type --}}
                                    <div class="space-y-1.5"
                                         x-data="{
                                            open: false,
                                            search: '',
                                            selectedType: '2',
                                            options: [
                                                { value: '1', title: 'SHA-1 (1)' },
                                                { value: '2', title: 'SHA-256 (2)', subtitle: 'استاندارد و امن' },
                                                { value: '3', title: 'GOST R 34.11-94 (3)' },
                                                { value: '4', title: 'SHA-384 (4)', subtitle: 'پیشرفته' }
                                            ],
                                            get filteredOptions() {
                                                if (!this.search.trim()) return this.options;
                                                const q = this.search.toLowerCase();
                                                return this.options.filter(o =>
                                                    o.title.toLowerCase().includes(q) ||
                                                    (o.subtitle && o.subtitle.toLowerCase().includes(q)) ||
                                                    o.value.includes(q)
                                                );
                                            },
                                            select(opt) {
                                                this.selectedType = opt ? String(opt.value) : '2';
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedTitle() {
                                                let found = this.options.find(o => String(o.value) === String(this.selectedType));
                                                if (!found) return 'انتخاب نوع دایجست...';
                                                return found.title + (found.subtitle ? ' (' + found.subtitle + ')' : '');
                                            }
                                         }">
                                        <label class="{{ $labelClass }}">نوع دایجست (Digest Type) <span
                                                class="text-rose-500 font-black">*</span></label>
                                        <div class="relative" :class="{ 'z-50': open }">
                                            <input type="hidden" name="digestType" :value="selectedType" required>

                                            <button type="button" @click="open = !open"
                                                    class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                                                <span x-text="getSelectedTitle()" class="truncate font-medium"
                                                      dir="ltr"></span>
                                                <svg
                                                    class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                                    :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[260px] sm:min-w-[300px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                                    <input type="text" x-model="search"
                                                           placeholder="جستجوی نوع دایجست..."
                                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                                </div>

                                                <template x-for="opt in filteredOptions" :key="opt.value">
                                                    <div @click="select(opt)"
                                                         class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedType) === String(opt.value) }">
                                                        <span x-text="opt.title" class="truncate font-medium"
                                                              dir="ltr"></span>
                                                        <span x-show="opt.subtitle"
                                                              class="text-[10px] px-1.5 py-0.5 rounded border border-gray-200/80 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shrink-0 font-medium"
                                                              x-text="opt.subtitle"></span>
                                                    </div>
                                                </template>

                                                <div x-show="filteredOptions.length === 0"
                                                     class="p-3 text-xs text-gray-400 text-center">
                                                    هیچ گزینه‌ای پیدا نشد
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">مقدار هش دایجست (Digest Key Hex) <span
                                            class="text-rose-500 font-black">*</span></label>
                                    <input type="text" name="digest" required
                                           placeholder="مثال: AFE249392848D0458B3967BA1EE9BDEA6C3ECB43"
                                           class="{{ $inputClass }} text-start uppercase" dir="ltr">
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit"
                                            class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95 cursor-pointer">
                                        ثبت رکورد DNSSEC
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        {{-- General Specs Card --}}
        <div class="{{ $cardClass }} overflow-hidden flex flex-col justify-between h-full">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <div
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span>اطلاعات کلی دامنه</span>
                </h2>
            </div>

            <div class="p-6 space-y-4 text-xs sm:text-sm flex-1 flex flex-col justify-between">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">شناسه سفارش:</span>
                    <span
                        class="font-bold text-xs text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700/60 px-2.5 py-1 rounded-lg"
                        dir="ltr">
                        {{ $domain->order_id ? $faNum($domain->order_id) : '—' }}
                    </span>
                </div>

                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">تاریخ ثبت:</span>
                    <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                        {{ $domain->creation_date ? $faNum(jdate($domain->creation_date)->format('Y/m/d')) : '—' }}
                    </span>
                </div>

                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">تاریخ انقضا:</span>
                    <span class="font-bold text-xs text-gray-900 dark:text-white">
                        {{ $domain->expiry_date ? $faNum(jdate($domain->expiry_date)->format('Y/m/d')) : '—' }}
                    </span>
                </div>

                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">مخفی‌سازی WHOIS:</span>
                    @if($domain->privacy_protected)
                        <span
                            class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 font-bold text-xs">
                            فعال (پنهان)
                        </span>
                    @else
                        <span
                            class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400 font-bold text-xs">
                            غیرفعال (عمومی)
                        </span>
                    @endif
                </div>

                {{-- Transfer Lock Status & Toggle Form --}}
                @php
                    $isLocked = filter_var($config['lock'] ?? false, FILTER_VALIDATE_BOOLEAN);
                @endphp
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">قفل انتقال:</span>
                    <form action="{{ route('user.domainmanager.domains.toggle-lock', $domain->domain_name) }}"
                          method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="lock" value="{{ $isLocked ? '0' : '1' }}">
                        <button type="submit"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer {{ $isLocked ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200/60' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60' }}">
                            {{ $isLocked ? 'قفل است (باز کردن)' : 'آزاد است (قفل کردن)' }}
                        </button>
                    </form>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">آخرین همگام‌سازی:</span>
                    <span class="text-xs text-gray-400 font-bold">
                        {{ $domain->synced_at ? $faNum(jdate($domain->synced_at)->format('Y/m/d H:i')) : '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Nameserver Update Form Card --}}
        <div class="{{ $cardClass }} overflow-hidden flex flex-col justify-between h-full">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                    </span>
                    <span>تغییر کارگزارهای نام دامنه (Nameservers)</span>
                </h2>
            </div>

            <form method="POST" action="{{ route('user.domainmanager.domains.modify-ns', $domain->domain_name) }}"
                  class="p-6 flex-1 flex flex-col justify-between space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $labelClass }}">Nameserver 1 <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="text" name="ns1" required
                               value="{{ old('ns1', $domain->nameservers[0] ?? '') }}"
                               class="{{ $inputClass }} text-start" dir="ltr" placeholder="ns1.example.com">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nameserver 2 <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="text" name="ns2" required
                               value="{{ old('ns2', $domain->nameservers[1] ?? '') }}"
                               class="{{ $inputClass }} text-start" dir="ltr" placeholder="ns2.example.com">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nameserver 3 <span
                                class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span></label>
                        <input type="text" name="ns3" value="{{ old('ns3', $domain->nameservers[2] ?? '') }}"
                               class="{{ $inputClass }} text-start" dir="ltr" placeholder="اختیاری">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nameserver 4 <span
                                class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span></label>
                        <input type="text" name="ns4" value="{{ old('ns4', $domain->nameservers[3] ?? '') }}"
                               class="{{ $inputClass }} text-start" dir="ltr" placeholder="اختیاری">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex justify-end">
                    <button type="submit"
                            class="w-full sm:w-auto px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>بروزرسانی دی‌ان‌اس‌ها در کارگزار</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Row 2: 3-Column Highlights Row: Customer, Order & Active DNS (Under Row 1) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch" x-data="domainConnectionsManager()">
        {{-- Card 1: Connected CRM Customer --}}
        <div class="{{ $cardClass }} overflow-visible relative z-30 flex flex-col justify-between h-full">
            <div
                class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                    <div
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span>مشتری متصل CRM</span>
                </h2>
            </div>

            <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-between">
                <form x-ref="domainClientFullForm"
                      action="{{ route('user.domainmanager.domains.update-client', $domain->domain_name) }}"
                      method="POST">
                    @csrf
                    <input type="hidden" name="client_id" :value="selectedCustomerId">

                    <label class="{{ $labelClass }}">
                        انتخاب پرونده مشتری <span
                            class="text-xs font-normal text-gray-400 ms-1">(جهت اتصال به CRM)</span>
                    </label>

                    {{-- Searchable Dropdown Button --}}
                    <div class="relative" :class="{ 'z-50': openCustomer }">
                        <button type="button" @click="openCustomer = !openCustomer"
                                class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                            <div class="flex items-center gap-2.5 truncate">
                                <span
                                    class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <template x-if="selectedCustomer">
                                    <span class="truncate font-bold text-gray-900 dark:text-white"
                                          x-text="selectedCustomer.name"></span>
                                </template>
                                <template x-if="!selectedCustomer">
                                    <span class="text-gray-400 font-medium">انتخاب یا اتصال مشتری...</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ms-2">
                                <template x-if="selectedCustomer">
                                    <button type="button" @click.stop="clearCustomer()" title="حذف اتصال مشتری"
                                            class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-gray-400 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </template>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                     :class="{ 'rotate-180': openCustomer }" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="openCustomer" @click.outside="openCustomer = false" x-cloak
                             class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="searchCustomer"
                                       placeholder="جستجوی نام، ایمیل، شماره یا نام کاربری..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            {{-- Option for Disconnecting / No Customer --}}
                            <div @click="clearCustomer()"
                                 class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 cursor-pointer transition-colors flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 mb-1 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span>بدون مشتری (حذف اتصال)</span>
                            </div>

                            {{-- Customers List --}}
                            <template x-for="c in filteredCustomers" :key="c.id">
                                <div @click="selectCustomer(c)"
                                     class="px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                     :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedCustomerId) === String(c.id) }">
                                    <div class="min-w-0">
                                        <div class="truncate font-bold" x-text="c.name"></div>
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 truncate"
                                             x-text="[c.phone, c.email].filter(Boolean).join(' • ')"></div>
                                    </div>
                                    <span x-show="String(selectedCustomerId) === String(c.id)"
                                          class="text-indigo-600 dark:text-indigo-400 font-black shrink-0">✓</span>
                                </div>
                            </template>

                            <div x-show="filteredCustomers.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ مشتری یافت نشد
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Connected Customer Details Card --}}
                <template x-if="selectedCustomer">
                    <div
                        class="p-3.5 rounded-2xl border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-500/10 flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                      x-text="selectedCustomer.name"></span>
                                <span
                                    class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 shrink-0">متصل</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5"
                               x-text="[selectedCustomer.phone, selectedCustomer.email].filter(Boolean).join(' • ') || 'اطلاعات تماس ثبت نشده'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Card 2: Connected CRM Order --}}
        <div class="{{ $cardClass }} overflow-visible relative z-20 flex flex-col justify-between h-full">
            <div
                class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                    <div
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span>سفارش متصل در CRM</span>
                </h2>
                <template x-if="selectedCustomerId">
                    <span
                        class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 border border-indigo-200/50 truncate max-w-[120px]"
                        x-text="'فیلتر: ' + (selectedCustomer ? selectedCustomer.name : 'مشتری')"></span>
                </template>
            </div>

            <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-between">
                <form x-ref="domainOrderFullForm"
                      action="{{ route('user.domainmanager.domains.update-order', $domain->domain_name) }}"
                      method="POST">
                    @csrf
                    <input type="hidden" name="service_order_id" :value="selectedOrderId">

                    <label class="{{ $labelClass }}">
                        انتخاب سفارش دامنه <span class="text-xs font-normal text-gray-400 ms-1">(دسته دامنه)</span>
                    </label>

                    {{-- Searchable Dropdown Button --}}
                    <div class="relative" :class="{ 'z-50': openOrder }">
                        <button type="button" @click="openOrder = !openOrder"
                                class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                            <div class="flex items-center gap-2.5 truncate">
                                <span
                                    class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </span>
                                <template x-if="selectedOrder">
                                    <span class="truncate font-bold text-gray-900 dark:text-white"
                                          x-text="'سفارش #' + selectedOrder.order_number + ' (' + selectedOrder.service_name + ')'"></span>
                                </template>
                                <template x-if="!selectedOrder">
                                    <span class="text-gray-400 font-medium">انتخاب سفارش دامنه...</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ms-2">
                                <template x-if="selectedOrder">
                                    <button type="button" @click.stop="clearOrder()" title="حذف اتصال سفارش"
                                            class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-gray-400 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </template>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                     :class="{ 'rotate-180': openOrder }" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="openOrder" @click.outside="openOrder = false" x-cloak
                             class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">

                            {{-- Active Filter Notification --}}
                            <template x-if="selectedCustomerId && selectedCustomer">
                                <div
                                    class="px-3 py-1.5 mb-1 text-[11px] rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-between">
                                    <span>فقط سفارشات: <span x-text="selectedCustomer.name"></span></span>
                                    <span class="text-[10px] bg-indigo-200/60 dark:bg-indigo-900 px-1.5 py-0.2 rounded"
                                          x-text="filteredOrders.length + ' سفارش'"></span>
                                </div>
                            </template>

                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="searchOrder"
                                       placeholder="جستجوی شماره سفارش، عنوان یا مشتری..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            {{-- Option for Disconnecting / No Order --}}
                            <div @click="clearOrder()"
                                 class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 cursor-pointer transition-colors flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 mb-1 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span>بدون سفارش (حذف اتصال)</span>
                            </div>

                            {{-- Orders List --}}
                            <template x-for="o in filteredOrders" :key="o.id">
                                <div @click="selectOrder(o)"
                                     class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                     :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedOrderId) === String(o.id) }">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="text-indigo-600 dark:text-indigo-400 font-mono"
                                                  x-text="'#' + o.order_number"></span>
                                            <span class="truncate" x-text="o.service_name"></span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 truncate"
                                             x-text="o.client_name"></div>
                                    </div>
                                    <span x-show="String(selectedOrderId) === String(o.id)"
                                          class="text-indigo-600 dark:text-indigo-400 font-black shrink-0">✓</span>
                                </div>
                            </template>

                            <div x-show="filteredOrders.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ سفارش دامنه‌ای یافت نشد
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Connected Order Preview Card --}}
                @if($domain->service_order_id && $domain->serviceOrder)
                    <div
                        class="p-3.5 rounded-2xl border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-500/10 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-gray-900 dark:text-white">
                                    سفارش #{{ $domain->serviceOrder->order_number }}
                                </span>
                                <span
                                    class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 shrink-0">متصل</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                {{ $domain->serviceOrder->service?->name ?? 'سفارش دامنه' }}
                                • {{ $domain->serviceOrder->customer?->full_name ?? ($domain->serviceOrder->client_name ?? '') }}
                            </p>
                        </div>

                        <a href="{{ route('services.orders.show', $domain->service_order_id) }}" target="_blank"
                           class="p-2 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-gray-200 dark:border-gray-700 text-xs font-bold transition-colors shrink-0"
                           title="مشاهده صفحه سفارش">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 3: Active Nameservers Card --}}
        <div class="{{ $cardClass }} overflow-hidden flex flex-col justify-between h-full">
            <div
                class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                    <div
                        class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-xl">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                    </div>
                    <span>دی‌ان‌اس‌های فعال</span>
                </h2>
                <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 border border-emerald-200/50">
                    {{ count($domain->nameservers ?? []) }} رکورد
                </span>
            </div>

            <div class="p-5 sm:p-6 space-y-2 flex-1 flex flex-col justify-center">
                @forelse($domain->nameservers ?? [] as $ns)
                    <div
                        class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-800 dark:text-gray-200 select-all"
                        dir="ltr">
                        <span class="font-bold truncate">{{ $ns }}</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 ms-2"></span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">هنوز هیچ دی‌ان‌اسی ثبت نشده است.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.domainConnectionsManager = function () {
            return {
                openCustomer: false,
                searchCustomer: '',
                customers: {!! json_encode($customersListForJs, JSON_UNESCAPED_UNICODE) !!},
                selectedCustomerId: '{{ ($domain->client_id && $domain->client) ? (string)$domain->client_id : '' }}',

                openOrder: false,
                searchOrder: '',
                orders: {!! json_encode($ordersListForJs, JSON_UNESCAPED_UNICODE) !!},
                selectedOrderId: '{{ $domain->service_order_id ? (string)$domain->service_order_id : '' }}',

                get selectedCustomer() {
                    if (!this.selectedCustomerId) return null;
                    return this.customers.find(c => String(c.id) === String(this.selectedCustomerId)) || null;
                },

                get filteredCustomers() {
                    if (!this.searchCustomer.trim()) return this.customers;
                    const q = this.searchCustomer.toLowerCase().trim();
                    return this.customers.filter(c =>
                        (c.name && c.name.toLowerCase().includes(q)) ||
                        (c.email && c.email.toLowerCase().includes(q)) ||
                        (c.phone && c.phone.includes(q)) ||
                        (c.username && c.username.toLowerCase().includes(q))
                    );
                },

                get selectedOrder() {
                    if (!this.selectedOrderId) return null;
                    return this.orders.find(o => String(o.id) === String(this.selectedOrderId)) || null;
                },

                get filteredOrders() {
                    let list = this.orders;
                    // If a valid customer is selected, ONLY show orders belonging to this customer!
                    if (this.selectedCustomer) {
                        list = list.filter(o => String(o.customer_id) === String(this.selectedCustomer.id));
                    }
                    if (!this.searchOrder.trim()) return list;
                    const q = this.searchOrder.toLowerCase().trim();
                    return list.filter(o =>
                        (o.order_number && o.order_number.toLowerCase().includes(q)) ||
                        (o.service_name && o.service_name.toLowerCase().includes(q)) ||
                        (o.client_name && o.client_name.toLowerCase().includes(q))
                    );
                },

                selectCustomer(c) {
                    this.selectedCustomerId = c ? String(c.id) : '';
                    this.openCustomer = false;
                    this.searchCustomer = '';
                    this.$nextTick(() => this.$refs.domainClientFullForm.submit());
                },

                clearCustomer() {
                    this.selectedCustomerId = '';
                    this.openCustomer = false;
                    this.searchCustomer = '';
                    this.$nextTick(() => this.$refs.domainClientFullForm.submit());
                },

                selectOrder(o) {
                    this.selectedOrderId = o ? String(o.id) : '';
                    if (o && o.customer_id && !this.selectedCustomerId) {
                        this.selectedCustomerId = String(o.customer_id);
                    }
                    this.openOrder = false;
                    this.searchOrder = '';
                    this.$nextTick(() => this.$refs.domainOrderFullForm.submit());
                },

                clearOrder() {
                    this.selectedOrderId = '';
                    this.openOrder = false;
                    this.searchOrder = '';
                    this.$nextTick(() => this.$refs.domainOrderFullForm.submit());
                }
            };
        };
    </script>
@endpush
