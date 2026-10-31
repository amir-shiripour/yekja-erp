<div role="tabpanel" id="tabpanel-history" aria-labelledby="tab-history" x-show="activeTab === 'history'" x-cloak class="w-full">
    <x-resellerhub::card class="w-full">
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-violet-50/60 to-transparent dark:from-violet-950/20 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400 rounded-xl shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">تایملاین وقایع و
                        تاریخچه کامل سفارش</h3>
                    <p class="text-xs text-gray-400 mt-0.5">ثبت زمانی کلیه رخدادها، تراکنش‌های پرداختی، تحویل
                        سامانه و تغییرات سفارش</p>
                </div>
            </div>
            <span
                class="text-xs font-black px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                {{ reseller_fa_num($sortedTimelineEvents->count()) }} رویداد ثبت‌شده
                    </span>
        </div>

        <div class="p-6 sm:p-8">
            @if($sortedTimelineEvents->isNotEmpty())
                <div class="relative">
                    <div
                        class="absolute top-4 bottom-4 start-5 sm:start-6 w-0.5 bg-gray-200 dark:bg-gray-700/70"></div>

                    <div class="space-y-6 sm:space-y-8">
                        @foreach($sortedTimelineEvents as $index => $event)
                            <div class="relative flex items-start gap-4 sm:gap-6">
                                {{-- Event Icon Bubble --}}
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl {{ $event['icon_bg'] }} flex items-center justify-center shrink-0 z-10 ring-4 ring-white dark:ring-gray-900 transition-transform hover:scale-105">
                                    @if($event['type'] === 'online_purchase')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    @elseif($event['type'] === 'online_payment')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    @elseif($event['type'] === 'created')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    @elseif($event['type'] === 'upgrade_invoice' || $event['type'] === 'package_upgraded')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    @elseif($event['type'] === 'invoice' || $event['type'] === 'renewal_invoice')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @elseif($event['type'] === 'payment')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    @elseif($event['type'] === 'provisioned')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($event['type'] === 'provision_failed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @elseif($event['type'] === 'order_items_removed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    @elseif($event['type'] === 'order_items_added')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                            @endif
                                        </div>
                                        <div
                                            class="flex-1 p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-3 transition-shadow hover:shadow-xs">
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-sm font-black text-gray-900 dark:text-white">{{ $event['title'] }}</h4>
                                                    @if(!empty($event['badge']))
                                                        <span
                                                            class="text-xs font-bold px-2.5 py-0.5 rounded-md border {{ $event['badge_class'] }}">
                                                            {{ $event['badge'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                                    @if(!empty($event['user']))
                                                        <span
                                                            class="font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200/80 dark:border-gray-700/80">
                                                            {{ $event['user'] }}
                                                        </span>
                                                    @endif
                                                    <span class="tabular-nums dir-ltr text-right">
                                                        {{ $event['date'] ? reseller_format_jalali($event['date'], 'Y/m/d H:i') : '—' }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if(!empty($event['subtitle']))
                                                <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">{{ $event['subtitle'] }}</p>
                                            @endif

                                            {{-- Items List (Badges for each removed/added capability) --}}
                                            @if(!empty($event['items_list']))
                                                <div class="flex flex-wrap gap-2 pt-1">
                                                    @foreach($event['items_list'] as $itName)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black {{ ($event['type'] === 'order_items_removed') ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60' }}">
                                                            @if($event['type'] === 'order_items_removed')
                                                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0"
                                                                     fill="none" viewBox="0 0 24 24"
                                                                     stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0"
                                                                     fill="none" viewBox="0 0 24 24"
                                                                     stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            @endif
                                                            <span>{{ $itName }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Details Grid --}}
                                            @if(!empty($event['details']))
                                                <div
                                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-gray-200/60 dark:border-gray-800 text-xs">
                                                    @foreach($event['details'] as $k => $v)
                                                        @if($v)
                                                            @php
                                                                $isLongDetail = mb_strlen((string)$v) > 35 || in_array($k, ['شرح عملیات', 'توضیحات', 'جزئیات', 'پیام سیستم']);
                                                            @endphp
                                                            <div
                                                                class="p-2.5 rounded-xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/50 flex flex-col {{ $isLongDetail ? 'col-span-1 sm:col-span-2 lg:col-span-3' : '' }}">
                                                                <span
                                                                    class="text-xs font-bold text-gray-400 mb-0.5">{{ $k }}:</span>
                                                                <span
                                                                    class="font-black text-gray-800 dark:text-gray-200 {{ $isLongDetail ? 'whitespace-normal break-words leading-relaxed' : 'truncate' }}">{{ $v }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Link Action (if any) --}}
                                            @if(!empty($event['link']))
                                                <div class="pt-2 flex justify-end">
                                                    <a href="{{ $event['link'] }}"
                                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-black transition">
                                                        <span>{{ $event['link_text'] ?? 'مشاهده جزئیات' }}</span>
                                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-xs text-gray-400">
                    هنوز رویدادی برای این سفارش ثبت نشده است.
                </div>
            @endif
        </div>
    </x-resellerhub::card>
</div>
