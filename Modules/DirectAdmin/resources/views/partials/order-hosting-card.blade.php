@php
    use Modules\DirectAdmin\Entities\DaAccount;
    use Modules\DirectAdmin\Entities\DaServer;
    use Modules\DirectAdmin\Entities\DaSetting;

    $hostingAccount = $order->hostingAccount;
    $daServers = DaServer::orderBy('name')->get();
    $defaultServerId = DaSetting::get('default_server_id', $daServers->first()?->id);
    $defaultServer = $daServers->firstWhere('id', $defaultServerId) ?? $daServers->first();

    // Auto-detect domain from order or invoice meta if available
    $detectedDomain = '';
    if (isset($displayCustomFields)) {
        foreach ($displayCustomFields as $f) {
            if (str_contains(mb_strtolower($f['label']), 'دامن') || str_contains(mb_strtolower($f['label']), 'domain')) {
                $detectedDomain = preg_replace('/^https?:\/\//i', '', trim((string)$f['value']));
                $detectedDomain = preg_replace('/^www\./i', '', $detectedDomain);
                break;
            }
        }
    }
    if (!$detectedDomain && !empty($order->notes) && filter_var($order->notes, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        $detectedDomain = trim($order->notes);
    }

    $isHostingCategory = ($order->service?->category?->slug === 'hosting') || !empty($hostingAccount);

    $allExistingAccounts = DaAccount::with('server')->orderBy('username')->get();
    $existingAccountsJs = $allExistingAccounts->map(function ($a) use ($order) {
        return [
            'id' => (string)$a->id,
            'username' => $a->username,
            'domain' => $a->domain,
            'server_name' => $a->server->name ?? 'سرور',
            'package' => $a->package ?? 'default',
            'is_linked_other' => (!empty($a->order_id) && $a->order_id != $order->id),
            'order_id' => $a->order_id,
        ];
    })->values();
@endphp

@if($isHostingCategory)
    <div x-data="orderHostingManager()"
         class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden transition-all duration-300">
        {{-- Header matching Service Info Header --}}
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <span>سرویس هاستینگ دایرکت ادمین</span>
            </h3>
            @if($hostingAccount)
                <span
                    class="text-xs px-2.5 py-1 rounded-full font-bold {{ $hostingAccount->suspended ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' }}">
                {{ $hostingAccount->suspended ? 'مسدود' : 'فعال' }}
            </span>
            @endif
        </div>

        {{-- Body Content --}}
        <div class="p-6 space-y-6">
            @if($hostingAccount)
                {{-- Account is created and attached --}}
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                            <span class="block text-xs font-bold text-gray-400">نام کاربری هاست</span>
                            <span class="font-black text-gray-900 dark:text-white text-base block"
                                  dir="ltr">{{ $hostingAccount->username }}</span>
                        </div>

                        <div
                            class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                            <span class="block text-xs font-bold text-gray-400">دامنه متصل</span>
                            <a href="http://{{ $hostingAccount->domain }}" target="_blank"
                               class="font-black text-indigo-600 dark:text-indigo-400 text-base hover:underline truncate block"
                               dir="ltr">
                                {{ $hostingAccount->domain }}
                            </a>
                        </div>

                        <div
                            class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                            <span class="block text-xs font-bold text-gray-400">سرور میزبان</span>
                            <span
                                class="font-black text-gray-900 dark:text-white text-sm truncate block">{{ $hostingAccount->server->name ?? '—' }}</span>
                        </div>

                        <div
                            class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
                            <span class="block text-xs font-bold text-gray-400">پکیج هاست</span>
                            <span
                                class="font-black text-indigo-600 dark:text-indigo-400 text-sm block">{{ $hostingAccount->package ?? 'default' }}</span>
                        </div>
                    </div>

                    {{-- Quick Actions Bar --}}
                    <div
                        class="pt-4 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 dark:border-gray-700/50">
                        <span class="text-xs text-gray-400">همگام‌سازی وضعیت هاست و سفارش فعال است.</span>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="unlinkHost()" :disabled="linking"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span>قطع اتصال از سفارش</span>
                            </button>

                            <a href="{{ route('user.directadmin.accounts.show', $hostingAccount->username) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
                                <span>مدیریت اکانت</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- Empty state: No hosting created yet --}}
                <div class="text-center py-4 space-y-4">
                    <div
                        class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">اکانت هاستینگی برای این سفارش
                            متصل نشده است</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                            می‌توانید یک هاست جدید ایجاد نمایید یا یکی از هاست‌های موجود در دایرکت ادمین را به این سفارش
                            متصل کنید.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-3">
                        @if($daServers->isNotEmpty())
                            <button type="button" @click="openCreateModal = true"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all duration-200 active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ایجاد هاست جدید</span>
                            </button>
                        @endif

                        @if($allExistingAccounts->isNotEmpty())
                            <button type="button" @click="openLinkModal = true"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/60 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 font-black text-xs sm:text-sm shadow-xs transition-all duration-200 active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span>اتصال به هاست موجود</span>
                            </button>
                        @endif
                    </div>

                    @if($daServers->isEmpty())
                        <div
                            class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-xs text-amber-800 dark:text-amber-400 font-bold max-w-xs mx-auto">
                            ابتدا باید از منوی دایرکت ادمین حداقل یک سرور ثبت و فعال نمایید.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Quick Create Modal --}}
        @if(!$hostingAccount && $daServers->isNotEmpty())
            <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                         @click="!creating && (openCreateModal = false)"></div>

                    <div
                        class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-xl w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-start">

                        {{-- Modal Header --}}
                        <div
                            class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white">ایجاد و اتصال هاست
                                        دایرکت
                                        ادمین</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">برای سفارش
                                        شماره {{ $order->order_number }}</p>
                                </div>
                            </div>
                            <button type="button" @click="openCreateModal = false" :disabled="creating"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitCreateHost()" class="space-y-4">
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <input type="hidden" name="client_id" value="{{ $order->customer_id }}">

                            {{-- Server Selection --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">سرور
                                    میزبان
                                    دایرکت ادمین <span class="text-rose-500">*</span></label>
                                <select x-model="form.da_server_id" required
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-bold">
                                    @foreach($daServers as $srv)
                                        <option value="{{ $srv->id }}">
                                            {{ $srv->name }} ({{ $srv->host }}){{ $srv->id == $defaultServerId ? ' — [پیش‌فرض]' : '' }}{{ $srv->isManual() ? ' — [دستی]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Domain & Username --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام
                                        دامنه
                                        اصلی <span class="text-rose-500">*</span></label>
                                    <input type="text" x-model="form.domain" @input="generateUsernameFromDomain()"
                                           required
                                           placeholder="example.com" dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام
                                        کاربری هاست <span class="text-rose-500">*</span></label>
                                    <input type="text" x-model="form.username" required placeholder="userexample"
                                           dir="ltr"
                                           maxlength="16"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-mono font-bold">
                                </div>
                            </div>

                            {{-- Email & Package --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">ایمیل
                                        مدیر هاست <span class="text-rose-500">*</span></label>
                                    <input type="email" x-model="form.email" required placeholder="info@example.com"
                                           dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">پکیج
                                        هاستینگ <span class="text-rose-500">*</span></label>
                                    <input type="text" x-model="form.package" required placeholder="default" dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-mono">
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">رمز عبور
                                        هاست
                                        <span class="text-rose-500">*</span></label>
                                    <button type="button" @click="genPassword()"
                                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        تولید رمز تصادفی قوی
                                    </button>
                                </div>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" x-model="form.password" required
                                           dir="ltr"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs sm:text-sm text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white font-mono pr-10">
                                    <button type="button" @click="showPass = !showPass"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div
                                class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="openCreateModal = false" :disabled="creating"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                    انصراف
                                </button>
                                <button type="submit" :disabled="creating"
                                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 cursor-pointer">
                                    <template x-if="creating">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="creating ? 'در حال ساخت هاست...' : 'تایید و راه‌اندازی هاست'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Link Existing Host Modal --}}
        @if(!$hostingAccount && $allExistingAccounts->isNotEmpty())
            <div x-show="openLinkModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                         @click="!linking && (openLinkModal = false)"></div>

                    <div
                        class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-start">

                        {{-- Modal Header --}}
                        <div
                            class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                            <span
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </span>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white">اتصال به هاست‌های موجود
                                        در سیستم</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">برای سفارش
                                        شماره {{ $order->order_number }}</p>
                                </div>
                            </div>
                            <button type="button" @click="openLinkModal = false" :disabled="linking"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Search Input --}}
                        <div>
                            <input type="text" x-model="searchExisting"
                                   placeholder="جستجوی نام کاربری، دامنه یا نام سرور..."
                                   class="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        </div>

                        {{-- Accounts List --}}
                        <div class="max-h-80 overflow-y-auto space-y-2.5 pe-1">
                            <template x-for="acc in filteredExistingAccounts" :key="acc.id">
                                <div
                                    class="p-3.5 rounded-2xl border border-gray-100 dark:border-gray-800 hover:border-indigo-200 dark:hover:border-indigo-500/40 bg-gray-50/60 dark:bg-gray-900/40 flex items-center justify-between gap-4 transition-all">
                                    <div class="min-w-0 flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center justify-center shrink-0">
                                            <span x-text="acc.username.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-sm text-gray-900 dark:text-white"
                                                      x-text="acc.username"></span>
                                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold"
                                                      dir="ltr" x-text="'(' + acc.domain + ')'"></span>
                                            </div>
                                            <div
                                                class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 flex items-center gap-2">
                                                <span x-text="'سرور: ' + acc.server_name"></span>
                                                <span>•</span>
                                                <span x-text="'پکیج: ' + acc.package"></span>
                                                <template x-if="acc.is_linked_other">
                                                    <span class="text-amber-500 font-bold">(متصل به سفارش دیگر)</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="submitLinkHost(acc)" :disabled="linking"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-all active:scale-95 shrink-0 cursor-pointer">
                                        <span>اتصال به این سفارش</span>
                                    </button>
                                </div>
                            </template>

                            <div x-show="filteredExistingAccounts.length === 0"
                                 class="py-8 text-center text-xs text-gray-400">
                                هیچ اکانت هاستی با این مشخصات یافت نشد.
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-4 flex items-center justify-end border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="openLinkModal = false" :disabled="linking"
                                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                بستن
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function orderHostingManager() {
            return {
                openCreateModal: false,
                openLinkModal: false,
                creating: false,
                linking: false,
                showPass: false,
                searchExisting: '',
                existingAccounts: {!! json_encode($existingAccountsJs, JSON_UNESCAPED_UNICODE) !!},
                form: {
                    da_server_id: '{{ $defaultServerId ?? '' }}',
                    domain: '{{ $detectedDomain }}',
                    username: '',
                    email: '{{ $order->customer?->email ?? ($detectedDomain ? "info@" . $detectedDomain : "") }}',
                    package: 'default',
                    password: '',
                    order_id: '{{ $order->id }}',
                    client_id: '{{ $order->customer_id ?? "" }}'
                },
                init() {
                    if (this.form.domain && !this.form.username) {
                        this.generateUsernameFromDomain();
                    }
                    this.genPassword();
                },
                get filteredExistingAccounts() {
                    if (!this.searchExisting.trim()) return this.existingAccounts;
                    const q = this.searchExisting.toLowerCase().trim();
                    return this.existingAccounts.filter(a =>
                        (a.username && a.username.toLowerCase().includes(q)) ||
                        (a.domain && a.domain.toLowerCase().includes(q)) ||
                        (a.server_name && a.server_name.toLowerCase().includes(q))
                    );
                },
                generateUsernameFromDomain() {
                    if (!this.form.domain) return;
                    let clean = this.form.domain.toLowerCase().replace(/[^a-z0-9]/g, '');
                    clean = clean.substring(0, 10);
                    if (clean && /^[0-9]/.test(clean)) {
                        clean = 'u' + clean.substring(0, 9);
                    }
                    this.form.username = clean || 'user' + Math.floor(Math.random() * 899 + 100);
                },
                genPassword() {
                    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
                    let pass = '';
                    for (let i = 0; i < 16; i++) {
                        pass += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    this.form.password = pass;
                },
                async submitCreateHost() {
                    if (this.creating) return;
                    this.creating = true;

                    try {
                        const response = await fetch('{{ route("user.directadmin.accounts.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            alert(data.message || 'هاست با موفقیت در دایرکت ادمین ایجاد گردید.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'خطا در ایجاد اکانت هاستینگ.');
                        }
                    } catch (err) {
                        alert('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.');
                    } finally {
                        this.creating = false;
                    }
                },
                async submitLinkHost(acc) {
                    if (this.linking) return;
                    if (!confirm(`آیا از اتصال اکانت هاست ${acc.username} (${acc.domain}) به این سفارش اطمینان دارید؟`)) {
                        return;
                    }
                    this.linking = true;
                    try {
                        const response = await fetch('{{ route("user.directadmin.accounts.link-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: '{{ $order->id }}',
                                account_id: acc.id
                            })
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            alert(data.message || 'هاست با موفقیت به سفارش متصل گردید.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'خطا در اتصال هاست به سفارش.');
                        }
                    } catch (e) {
                        alert('خطا در ارتباط با سرور.');
                    } finally {
                        this.linking = false;
                    }
                },
                async unlinkHost() {
                    if (this.linking) return;
                    if (!confirm('آیا از قطع اتصال این اکانت هاستینگ از سفارش اطمینان دارید؟ (اکانت هاست در سرور حذف نخواهد شد)')) {
                        return;
                    }
                    this.linking = true;
                    try {
                        const response = await fetch('{{ route("user.directadmin.accounts.unlink-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: '{{ $order->id }}',
                                account_id: '{{ $hostingAccount->id ?? "" }}'
                            })
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            alert(data.message || 'اتصال هاست از سفارش حذف گردید.');
                            window.location.reload();
                        } else {
                            alert(data.message || 'خطا در قطع اتصال هاست.');
                        }
                    } catch (e) {
                        alert('خطا در ارتباط با سرور.');
                    } finally {
                        this.linking = false;
                    }
                }
            };
        }
    </script>
@endif
