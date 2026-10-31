@php use Modules\ResellerHub\App\Helpers\ResellerHelper; @endphp
<div>
    @if($errorMessage)
        <div
            class="mb-5 rounded-2xl bg-rose-50/90 dark:bg-rose-950/40 p-3.5 px-4 text-xs font-medium text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-900/50 flex items-start gap-3 shadow-xs">
            <svg class="w-4 h-4 shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="leading-relaxed">{{ $errorMessage }}</span>
        </div>
    @endif

    <form wire:submit.prevent="{{ $step === 'mobile' ? 'submitMobile' : 'verifyOtp' }}" class="space-y-5">
        @if($step === 'mobile')
            <div class="space-y-4">
                <div>
                    <label for="mobile" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                        شماره موبایل خود را وارد کنید
                    </label>
                    <div class="relative">
                        <input wire:model="mobile"
                               id="mobile"
                               type="tel"
                               dir="ltr"
                               autofocus
                               placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                               class="block w-full rounded-xl border border-gray-200 bg-gray-50/60 py-3.5 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-850 text-left font-semibold"/>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    @error('mobile') <p
                        class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p> @enderror
                    <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 leading-relaxed">
                        کد تایید یک‌بار مصرف برای این شماره پیامک خواهد شد.
                    </p>
                </div>
            </div>
        @endif
        @if($step === 'otp_login' || $step === 'name_otp_register')
            <div class="space-y-4">
                <div
                    class="flex items-center justify-between p-3 px-3.5 rounded-xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 transition-all">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div
                            class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[11px] text-gray-400 dark:text-gray-500">شماره مقصد:</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white tracking-wide leading-tight"
                                 dir="ltr">
                                {{ $this->getFormattedMobile() }}
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            wire:click="resetToMobile"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50/80 dark:text-indigo-400 dark:hover:bg-indigo-950/50 transition-all cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span>ویرایش شماره</span>
                    </button>
                </div>
                @if($isExistingUser)
                    <div
                        class="flex items-center gap-2 p-2.5 px-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 text-xs border border-indigo-100/80 dark:border-indigo-900/50">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></div>
                        <span
                            class="font-medium">شماره شما در سیستم فعال است؛ با ورود کد پیامکی مستقیماً وارد می‌شوید.</span>
                    </div>
                @else
                    <div>
                        <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            نام و نام خانوادگی
                        </label>
                        <input wire:model="name"
                               id="name"
                               type="text"
                               placeholder="مثال: علی محمدی"
                               class="block w-full rounded-xl border border-gray-200 bg-gray-50/60 py-3 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-850 font-medium"/>
                        @error('name') <p
                            class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div class="pt-1">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2.5">
                        کد تایید ۵ رقمی
                    </label>

                    <div x-data="{
                            otp: '',
                            inputs: [],
                            init() {
                                this.$nextTick(() => {
                                    this.inputs = Array.from(this.$refs.container.querySelectorAll('input[type=text]'));
                                    if(this.inputs.length > 0) this.inputs[0].focus();
                                });
                            },
                            handleInput(e, i) {
                                let val = e.target.value.replace(/[^0-9]/g, '');
                                e.target.value = val;
                                if (val && i < 4) this.inputs[i + 1].focus();
                                this.updateCode();
                                if (this.otp.length === 5) {
                                    this.$wire.verifyOtp();
                                }
                            },
                            handleBackspace(e, i) {
                                if (!e.target.value && i > 0) {
                                    this.inputs[i - 1].focus();
                                    this.inputs[i - 1].value = '';
                                }
                                this.updateCode();
                            },
                            handlePaste(e) {
                                e.preventDefault();
                                let text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 5);
                                for (let i = 0; i < text.length; i++) {
                                    if(this.inputs[i]) this.inputs[i].value = text[i];
                                }
                                this.updateCode();
                                let focusIndex = Math.min(text.length, 4);
                                if (this.inputs[focusIndex]) this.inputs[focusIndex].focus();
                                if (this.otp.length === 5) {
                                    this.$wire.verifyOtp();
                                }
                            },
                            updateCode() {
                                this.otp = this.inputs.map(i => i.value).join('');
                                this.$refs.hiddenInput.value = this.otp;
                                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }"
                         class="flex justify-between gap-2 sm:gap-3" dir="ltr" x-ref="container">

                        <input type="hidden" wire:model="otp" x-ref="hiddenInput"/>

                        @for ($i = 0; $i < 5; $i++)
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   @input="handleInput($event, {{ $i }})"
                                   @keydown.backspace="handleBackspace($event, {{ $i }})"
                                   @paste="handlePaste($event)"
                                   class="w-full h-13 sm:h-14 text-center text-xl font-bold rounded-xl border border-gray-200 bg-gray-50/70 text-gray-900 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-800 dark:bg-gray-800 dark:text-white dark:focus:border-indigo-500 dark:focus:bg-gray-850 shadow-2xs"/>
                        @endfor
                    </div>
                    @error('otp') <p
                        class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p> @enderror
                    <div x-data="{
                            timer: 120,
                            total: 120,
                            interval: null,
                            circumference: 75.4,
                            init() {
                                this.startTimer(120);
                            },
                            startTimer(sec = 120) {
                                this.total = sec;
                                this.timer = sec;
                                clearInterval(this.interval);
                                this.interval = setInterval(() => {
                                    if (this.timer > 0) {
                                        this.timer--;
                                    } else {
                                        clearInterval(this.interval);
                                    }
                                }, 1000);
                            },
                            get ringOffset() {
                                if (this.total <= 0) return 0;
                                let progress = this.timer / this.total;
                                return this.circumference * (1 - progress);
                            },
                            formatTimer() {
                                let m = Math.floor(this.timer / 60);
                                let s = this.timer % 60;
                                let str = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                                return str.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                            }
                        }"
                         x-on:otp-timer-reset.window="startTimer($event.detail?.duration || 120)"
                         class="mt-4">
                        <div x-show="timer > 0"
                             class="flex items-center justify-between p-3 px-3.5 rounded-xl bg-slate-50/80 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="relative w-8 h-8 flex items-center justify-center shrink-0">
                                    <svg class="w-8 h-8 transform -rotate-90" viewBox="0 0 32 32">
                                        <defs>
                                            <linearGradient id="timerDialGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stop-color="#4f46e5"/>
                                                <stop offset="100%" stop-color="#818cf8"/>
                                            </linearGradient>
                                        </defs>
                                        <circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2.2"
                                                class="text-indigo-100 dark:text-gray-700/80 fill-none"/>
                                        <circle cx="16" cy="16" r="12" stroke="url(#timerDialGrad)" stroke-width="2.5"
                                                stroke-linecap="round"
                                                class="fill-none transition-all duration-1000 ease-linear"
                                                :stroke-dasharray="circumference"
                                                :stroke-dashoffset="ringOffset"/>
                                    </svg>
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 absolute"
                                         viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="1.3" fill="currentColor"/>
                                        <line x1="8" y1="8" x2="8" y2="4" stroke="currentColor" stroke-width="1.4"
                                              stroke-linecap="round"/>
                                        <line x1="8" y1="8" x2="10.8" y2="9.6" stroke="currentColor" stroke-width="1.4"
                                              stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 leading-none">کد را دریافت نکردید؟</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 leading-tight mt-1">امکان ارسال مجدد تا:</span>
                                </div>
                            </div>
                            <div
                                class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-100 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs tracking-wider">
                                <span x-text="formatTimer()"></span>
                            </div>
                        </div>
                        <div x-show="timer <= 0" style="display: none;" class="w-full">
                            <button type="button"
                                    wire:click="resendOtp"
                                    wire:loading.attr="disabled"
                                    wire:target="resendOtp"
                                    class="group w-full py-3 px-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all active:scale-[0.99] cursor-pointer">
                                <span wire:loading.remove wire:target="resendOtp"
                                      class="inline-flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4 text-indigo-600 dark:text-indigo-400 group-hover:rotate-180 transition-transform duration-500 shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>ارسال مجدد کد تایید (SMS)</span>
                                </span>
                                <span wire:loading.flex wire:target="resendOtp"
                                      class="items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>در حال ارسال پیامک مجدد...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                    @if($this->isLocalDev())
                        <div
                            class="mt-3 p-2.5 px-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between">
                            <span class="font-medium text-[11px]">کد تایید لوکال (تست):</span>
                            <span
                                class="font-bold tracking-widest px-2 py-0.5 bg-amber-500/15 rounded-md text-amber-900 dark:text-amber-100">
                                {{ ResellerHelper::toPersianDigits($devOtpHint ?: '11111') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        <button type="submit"
                wire:loading.attr="disabled"
                class="group relative flex w-full justify-center items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 py-3.5 px-4 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 transition-all active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer">
            <span wire:loading.remove wire:target="submitMobile, verifyOtp">
                @if($step === 'mobile')
                    دریافت کد تایید
                @elseif($isExistingUser)
                    ورود به حساب کاربری
                @else
                    تایید و ایجاد سامانه
                @endif
            </span>
            <span wire:loading.flex wire:target="submitMobile, verifyOtp" class="items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>لطفاً صبر کنید...</span>
            </span>
        </button>
    </form>
    @if($step === 'mobile')
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-gray-400 dark:bg-gray-900">یا</span>
                </div>
            </div>

            <button type="button"
                    wire:click.prevent="$set('errorMessage', 'ورود با گوگل در این نسخه در حال توسعه است.')"
                    class="mt-5 flex w-full items-center justify-center gap-2.5 rounded-xl border border-gray-200 bg-white py-3 px-4 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition-all active:scale-[0.99] cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4"
                          d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853"
                          d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05"
                          d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335"
                          d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>ورود سریع با حساب گوگل</span>
            </button>
        </div>
    @endif
</div>
