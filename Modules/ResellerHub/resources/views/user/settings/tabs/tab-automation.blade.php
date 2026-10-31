@php
    $categories = [
        [
            'title' => 'چرخه سفارش و وضعیت',
            'dot_color' => 'bg-emerald-500',
            'border_class' => 'border-gray-100 dark:border-gray-700/60',
            'bg_class' => 'bg-gray-50/70 dark:bg-gray-900/50',
            'title_color' => 'text-gray-800 dark:text-gray-200',
            'item_title_color' => 'text-gray-900 dark:text-white',
            'items' => [
                ['title' => 'صدور فاکتور جدید', 'desc' => 'انتقال خودکار وضعیت سفارش به «در انتظار پرداخت»'],
                ['title' => 'تسویه و پرداخت فاکتور', 'desc' => 'فعال‌سازی آنی سفارش نمایندگی'],
                ['title' => 'لغو فاکتور', 'desc' => 'لغو خودکار سفارش متصل'],
                ['title' => 'لغو پرداخت و تراکنش', 'desc' => 'تعلیق سفارش و بازگشت به در انتظار'],
            ],
        ],
        [
            'title' => 'معوقات و سیاست Dunning',
            'dot_color' => 'bg-amber-500',
            'border_class' => 'border-gray-100 dark:border-gray-700/60',
            'bg_class' => 'bg-gray-50/70 dark:bg-gray-900/50',
            'title_color' => 'text-gray-800 dark:text-gray-200',
            'item_title_color' => 'text-gray-900 dark:text-white',
            'items' => [
                ['title' => 'معوقه شدن فاکتور در سررسید', 'desc' => 'معوق شدن فاکتور و غیرفعال‌سازی سفارش'],
                ['title' => 'لغو پس از مهلت ارفاقی (۷ روز)', 'desc' => 'لغو قطعی سفارش پس از ۷ روز معوق ماندن'],
                ['title' => 'بازگشت فاکتور به معوقه', 'desc' => 'بازگشت به معوقه با ابطال تراکنش'],
                ['title' => 'یادآوری معوقه ۳ روزه (Dunning)', 'desc' => 'پیگیری و ارسال پیامک پرداخت ۳ روز پس از سررسید'],
            ],
        ],
        [
            'title' => 'تمدید و صدور فاکتور',
            'dot_color' => 'bg-indigo-500',
            'border_class' => 'border-gray-100 dark:border-gray-700/60',
            'bg_class' => 'bg-gray-50/70 dark:bg-gray-900/50',
            'title_color' => 'text-gray-800 dark:text-gray-200',
            'item_title_color' => 'text-gray-900 dark:text-white',
            'items' => [
                ['title' => 'صدور خودکار فاکتور تمدید', 'desc' => 'ساخت خودکار فاکتور تمدید با سررسید ۷ روزه'],
                ['title' => 'بازگشت فاکتور به در انتظار', 'desc' => 'بازگشت فاکتور به در انتظار در لغو پرداخت'],
            ],
        ],
        [
            'title' => 'اطلاع‌رسانی پیامکی و رویدادها',
            'dot_color' => 'bg-blue-500 animate-pulse',
            'border_class' => 'border-indigo-200/60 dark:border-indigo-800/40',
            'bg_class' => 'bg-indigo-50/50 dark:bg-indigo-950/30',
            'title_color' => 'text-indigo-700 dark:text-indigo-300',
            'item_title_color' => 'text-indigo-950 dark:text-indigo-200',
            'items' => [
                ['title' => 'پیامک یادآوری ۷ روز قبل', 'desc' => 'اطلاع‌رسانی نزدیک شدن تاریخ انقضا به مشتری'],
                ['title' => 'هشدار فوری ۳ روز قبل', 'desc' => 'یادآوری اضطراری تمدید پیش از سررسید'],
                ['title' => 'پیامک صدور فاکتور', 'desc' => 'ارسال شماره فاکتور و لینک مستقیم پرداخت'],
                ['title' => 'پیامک غیرفعال‌سازی امکانات', 'desc' => 'هشدار قفل ماژول‌ها در سررسید فاکتور با لینک تسویه'],
                ['title' => 'پیامک تایید دریافت وجه', 'desc' => 'ارسال تاییدیه تسویه و فعال‌سازی سرویس'],
                ['title' => 'پیامک تحویل و خوش‌آمدگویی', 'desc' => 'تبریک تحویل سفارش و آماده‌سازی سامانه جدید'],
                ['title' => 'هشدار تکمیل سهمیه ۹۰٪', 'desc' => 'اطلاع‌رسانی آستانه مصرف منابع و پیشنهاد ارتقا'],
                ['title' => 'پیگیری تراکنش ناموفق', 'desc' => 'ارسال لینک پرداخت مجدد در خطای درگاه آنلاین'],
            ],
        ],
    ];

    $totalWorkflowsCount = collect($categories)->sum(fn($cat) => count($cat['items']));
    $globalWorkflowIndex = 0;
@endphp

<div role="tabpanel" id="tabpanel-automation" aria-labelledby="tab-automation" x-show="activeTab === 'automation'" x-cloak class="space-y-6">
    <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">اتوماسیون و گردش کارهای نمایندگی</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        مدیریت @fa($totalWorkflowsCount) گردش کار استاندارد برای پردازش خودکار فاکتورها، پیامک‌های یادآوری، تعلیق و تایید پرداخت مشتریان
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('user.workflows.index') }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all cursor-pointer">
                    <span>مدیریت پیشرفته در گردش کارها</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <button type="button"
                        @click="confirmSeedModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md shadow-indigo-500/20 transition-all text-xs font-bold cursor-pointer group">
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>نصب یا بازنشانی @fa($totalWorkflowsCount) گردش کار</span>
                </button>
            </div>
        </div>

        {{-- Notice Bar --}}
        <div
            class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-800/40 flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed">
                <strong>یکپارچگی کامل با سیستم مرکزی کرون، اسکنرها و پیامک:</strong> تمامی @fa($totalWorkflowsCount)
                گردش کار استاندارد با تسک زمان‌بندی‌شده
                <code
                    class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 font-mono text-xs text-indigo-800 dark:text-indigo-300">workflows:process</code>
                و دستورات تخصصی اسکنر
                <code
                    class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 font-mono text-xs text-indigo-800 dark:text-indigo-300">reseller:scan-overdue</code>
                و
                <code
                    class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 font-mono text-xs text-indigo-800 dark:text-indigo-300">reseller:scan-expiry</code>
                در کرون جاب سیستم همگام هستند و رویدادهای روزانه، وصول مطالبات (Dunning)، تعلیق و هشدارهای سهمیه بدون
                نیاز به دخالت دستی اجرا می‌شوند.
            </div>
        </div>

        {{-- Dynamic Seeded Workflows Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($categories as $category)
                <div
                    class="p-5 rounded-2xl {{ $category['bg_class'] }} border {{ $category['border_class'] }} space-y-3.5">
                    <h3 class="text-xs font-black {{ $category['title_color'] }} flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $category['dot_color'] }}"></span>
                        <span>{{ $category['title'] }} (@fa(count($category['items'])) مورد)</span>
                    </h3>
                    <ul class="text-xs space-y-3 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($category['items'] as $item)
                            @php $globalWorkflowIndex++; @endphp
                            <li class="pt-2 first:pt-0">
                                <span class="font-bold {{ $category['item_title_color'] }} block">
                                    @fa($globalWorkflowIndex). {{ $item['title'] }}:
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['desc'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </x-resellerhub::card>
</div>
