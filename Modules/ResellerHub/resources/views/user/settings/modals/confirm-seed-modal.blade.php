 <div x-show="confirmSeedModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div x-show="confirmSeedModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="confirmSeedModalOpen = false"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Box --}}
                <div x-show="confirmSeedModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700/60 p-6 sm:p-7">

                    <div class="flex items-start gap-4">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 sm:mx-0 shadow-sm border border-amber-200/50 dark:border-amber-700/40">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-black text-gray-900 dark:text-white" id="modal-title">
                                تایید نصب و بازنشانی گردش کارهای نمایندگی
                            </h3>
                            <div class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed space-y-2">
                                <p>
                                    آیا از اجرای عملیات نصب و بازنشانی <strong>۱۴ گردش کار استاندارد سامانه
                                        نمایندگی</strong> اطمینان دارید؟
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    با تایید این مرحله، تمامی ۱۴ گردش کار استاندارد زیر ثبت و مجدداً با سیستم مرکزی
                                    همگام‌سازی خواهند شد:
                                </p>
                                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 ps-1 font-medium">
                                    <li>۴ گردش کار چرخه تسویه، پرداخت، لغو و تغییر وضعیت سفارشات</li>
                                    <li>۳ گردش کار معوقات، مهلت ارفاقی ۷ روزه و لغو خودکار</li>
                                    <li>۲ گردش کار صدور خودکار فاکتور تمدید دوره و بازگشت وضعیت فاکتور</li>
                                    <li>۵ گردش کار پیامکی یادآوری تمدید، صدور فاکتور، تعلیق و تایید پرداخت مشتریان</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 sm:mt-7 flex flex-row-reverse items-center justify-start gap-3 border-t border-gray-100 dark:border-gray-700/60 pt-4">
                        <button type="button"
                                @click="document.getElementById('seedWorkflowsForm').submit()"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl border border-transparent px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-black text-white shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>بله، نصب و بازنشانی کن</span>
                        </button>
                        <button type="button"
                                @click="confirmSeedModalOpen = false"
                                class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all cursor-pointer">
                            انصراف
                        </button>
                    </div>
                </div>
            </div>
 </div>

 <form id="seedWorkflowsForm" action="{{ route('reseller.settings.seed-workflows') }}" method="POST"
       class="hidden">
     @csrf
 </form>
