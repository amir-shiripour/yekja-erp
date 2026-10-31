            <div x-show="currentStep === 3" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="p-6 sm:p-8 space-y-8">

                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۳</span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">انتخاب ماژول‌های فعال و
                                راه‌اندازی نهایی</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ماژول‌های مورد نیاز مشتری را
                                انتخاب
                                فرمایید (فیلترشده بر اساس سهمیه و ماژول‌های مجاز نماینده منتخب).</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-2 font-bold">
                            <button type="button" @click="selectAllAllowedModules()"
                                    class="text-indigo-600 dark:text-indigo-400 hover:underline">انتخاب همه مجازها
                            </button>
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <button type="button" @click="deselectAllModules()" class="text-gray-500 hover:underline">
                                لغو
                                انتخاب
                            </button>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 font-bold text-gray-600 dark:text-gray-300"
                            x-text="'مجاز: ' + faNum(allowedModulesCount()) + ' ماژول'"></span>
                    </div>
                </div>


    @include('resellerhub::user.tenants.partials.create-wizard.step-3.packages-showcase')
    @include('resellerhub::user.tenants.partials.create-wizard.step-3.modules-grid')
    @include('resellerhub::user.tenants.partials.create-wizard.step-3.invoice-calculation')
</div>
