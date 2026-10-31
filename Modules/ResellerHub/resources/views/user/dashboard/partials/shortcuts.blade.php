@php
    $shortcuts = [
        [
            'color' => 'indigo',
            'url' => route('reseller.tenants.create'),
            'title' => 'ساخت CRM جدید',
            'desc' => 'راه‌اندازی آنی و تحویل سامانه اختصاصی به مشتری',
            'badge' => null,
            'hover_border' => 'hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-indigo-500/10',
            'hover_text' => 'group-hover:text-indigo-600 dark:group-hover:text-indigo-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>',
        ],
        [
            'color' => 'teal',
            'url' => route('reseller.sub-accounts.index'),
            'title' => 'زیرمجموعه‌ها',
            'desc' => 'مدیریت اشخاص، کسب‌وکارها و نمایندگان زیردست',
            'badge' => reseller_fa_num($subAccountsCount) . ' حساب',
            'hover_border' => 'hover:border-teal-400 dark:hover:border-teal-500 hover:shadow-teal-500/10',
            'hover_text' => 'group-hover:text-teal-600 dark:group-hover:text-teal-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 21v-2a4 4 0 00-3-3.87"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3.13a4 4 0 010 7.75"/>',
        ],
        [
            'color' => 'violet',
            'url' => route('reseller.packages.index'),
            'title' => 'پکیج‌های ماژول',
            'desc' => 'بسته‌بندی امکانات و طرح‌های اشتراک دوره‌ای',
            'badge' => reseller_fa_num($activePackagesCount) . ' فعال',
            'hover_border' => 'hover:border-violet-400 dark:hover:border-violet-500 hover:shadow-violet-500/10',
            'hover_text' => 'group-hover:text-violet-600 dark:group-hover:text-violet-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        ],
        [
            'color' => 'rose',
            'url' => route('reseller.invoices.index'),
            'title' => 'فاکتورها و مالی',
            'desc' => 'صدور، پیگیری مطالبات و تسویه بانکی',
            'badge' => $unpaidInvoicesCount > 0 ? (reseller_fa_num($unpaidInvoicesCount) . ' باز') : null,
            'hover_border' => 'hover:border-rose-400 dark:hover:border-rose-500 hover:shadow-rose-500/10',
            'hover_text' => 'group-hover:text-rose-600 dark:group-hover:text-rose-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2zM7.5 8.5h.01m.99 5.5h.01"/>',
        ],
        [
            'color' => 'sky',
            'url' => route('reseller.orders.index'),
            'title' => 'سفارشات سامانه‌ها',
            'desc' => 'رهگیری سفارش‌های خرید و تمدید لایسنس',
            'badge' => $activeOrdersCount > 0 ? (reseller_fa_num($activeOrdersCount) . ' جاری') : null,
            'hover_border' => 'hover:border-sky-400 dark:hover:border-sky-500 hover:shadow-sky-500/10',
            'hover_text' => 'group-hover:text-sky-600 dark:group-hover:text-sky-400',
            'icon' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>',
        ],
        [
            'color' => 'amber',
            'url' => route('reseller.pricings.index'),
            'title' => 'تعرفه‌گذاری ماژول‌ها',
            'desc' => 'قیمت‌گذاری آیتمی و فعال‌سازی قابلیت‌ها',
            'badge' => null,
            'hover_border' => 'hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-amber-500/10',
            'hover_text' => 'group-hover:text-amber-600 dark:group-hover:text-amber-400',
            'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>',
        ],
        [
            'color' => 'cyan',
            'url' => route('reseller.tenants.export'),
            'title' => 'استخراج دیتابیس',
            'desc' => 'تهیه نسخه پشتیبان SQL و JSON از دیتابیس مشتریان',
            'badge' => 'SQL + JSON',
            'hover_border' => 'hover:border-cyan-400 dark:hover:border-cyan-500 hover:shadow-cyan-500/10',
            'hover_text' => 'group-hover:text-cyan-600 dark:group-hover:text-cyan-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
        ],
        [
            'color' => 'fuchsia',
            'url' => route('reseller.settings.index'),
            'title' => 'برندسازی و تنظیمات',
            'desc' => 'پیکربندی اختصاصی White-label، لوگو و درگاه',
            'badge' => 'وایت‌لیبل',
            'hover_border' => 'hover:border-fuchsia-400 dark:hover:border-fuchsia-500 hover:shadow-fuchsia-500/10',
            'hover_text' => 'group-hover:text-fuchsia-600 dark:group-hover:text-fuchsia-400',
            'icon' => '<circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>',
        ],
    ];
@endphp

<style>
    /* Scoped styling to guarantee 100% dark mode fidelity and eliminate washed-out white backgrounds */
    .hub-box-indigo { background-color: #eef2ff; border-color: #c7d2fe; color: #4f46e5; }
    .hub-badge-indigo { background-color: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    html.dark .hub-box-indigo { background-color: rgba(79, 70, 229, 0.16) !important; border-color: rgba(99, 102, 241, 0.35) !important; color: #818cf8 !important; }
    html.dark .hub-badge-indigo { background-color: rgba(30, 27, 75, 0.7) !important; border-color: rgba(99, 102, 241, 0.35) !important; color: #a5b4fc !important; }

    .hub-box-teal { background-color: #f0fdfa; border-color: #ccfbf1; color: #0d9488; }
    .hub-badge-teal { background-color: #f0fdfa; border-color: #99f6e4; color: #0f766e; }
    html.dark .hub-box-teal { background-color: rgba(20, 184, 166, 0.16) !important; border-color: rgba(20, 184, 166, 0.35) !important; color: #2dd4bf !important; }
    html.dark .hub-badge-teal { background-color: rgba(4, 47, 46, 0.7) !important; border-color: rgba(20, 184, 166, 0.35) !important; color: #5eead4 !important; }

    .hub-box-violet { background-color: #f5f3ff; border-color: #ddd6fe; color: #7c3aed; }
    .hub-badge-violet { background-color: #f5f3ff; border-color: #ddd6fe; color: #6d28d9; }
    html.dark .hub-box-violet { background-color: rgba(139, 92, 246, 0.16) !important; border-color: rgba(139, 92, 246, 0.35) !important; color: #a78bfa !important; }
    html.dark .hub-badge-violet { background-color: rgba(46, 16, 101, 0.7) !important; border-color: rgba(139, 92, 246, 0.35) !important; color: #c4b5fd !important; }

    .hub-box-rose { background-color: #fff1f2; border-color: #fecdd3; color: #e11d48; }
    .hub-badge-rose { background-color: #fff1f2; border-color: #fecdd3; color: #be123c; }
    html.dark .hub-box-rose { background-color: rgba(244, 63, 94, 0.16) !important; border-color: rgba(244, 63, 94, 0.35) !important; color: #fb7185 !important; }
    html.dark .hub-badge-rose { background-color: rgba(76, 5, 25, 0.7) !important; border-color: rgba(244, 63, 94, 0.35) !important; color: #fda4af !important; }

    .hub-box-sky { background-color: #f0f9ff; border-color: #bae6fd; color: #0284c7; }
    .hub-badge-sky { background-color: #f0f9ff; border-color: #bae6fd; color: #0369a1; }
    html.dark .hub-box-sky { background-color: rgba(14, 165, 233, 0.16) !important; border-color: rgba(14, 165, 233, 0.35) !important; color: #38bdf8 !important; }
    html.dark .hub-badge-sky { background-color: rgba(8, 47, 73, 0.7) !important; border-color: rgba(14, 165, 233, 0.35) !important; color: #7dd3fc !important; }

    .hub-box-amber { background-color: #fffbeb; border-color: #fde68a; color: #d97706; }
    .hub-badge-amber { background-color: #fffbeb; border-color: #fde68a; color: #b45309; }
    html.dark .hub-box-amber { background-color: rgba(245, 158, 11, 0.16) !important; border-color: rgba(245, 158, 11, 0.35) !important; color: #fbbf24 !important; }
    html.dark .hub-badge-amber { background-color: rgba(69, 26, 3, 0.7) !important; border-color: rgba(245, 158, 11, 0.35) !important; color: #fde68a !important; }

    .hub-box-cyan { background-color: #ecfeff; border-color: #cffafe; color: #0891b2; }
    .hub-badge-cyan { background-color: #ecfeff; border-color: #cffafe; color: #0e7490; }
    html.dark .hub-box-cyan { background-color: rgba(6, 182, 212, 0.16) !important; border-color: rgba(6, 182, 212, 0.35) !important; color: #22d3ee !important; }
    html.dark .hub-badge-cyan { background-color: rgba(22, 78, 99, 0.7) !important; border-color: rgba(6, 182, 212, 0.35) !important; color: #67e8f9 !important; }

    .hub-box-fuchsia { background-color: #fdf4ff; border-color: #f5d0fe; color: #c026d3; }
    .hub-badge-fuchsia { background-color: #fdf4ff; border-color: #f5d0fe; color: #a21caf; }
    html.dark .hub-box-fuchsia { background-color: rgba(217, 70, 239, 0.16) !important; border-color: rgba(217, 70, 239, 0.35) !important; color: #e879f9 !important; }
    html.dark .hub-badge-fuchsia { background-color: rgba(74, 4, 78, 0.7) !important; border-color: rgba(217, 70, 239, 0.35) !important; color: #f0abfc !important; }
</style>

<section class="space-y-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg hub-box-indigo flex items-center justify-center border">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </span>
            <h2 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                دسترسی سریع و کنسول ابزارهای نمایندگی
            </h2>
        </div>
        <span class="text-xs font-bold text-gray-400">{{ reseller_fa_num(8) }} ماژول عملیاتی فعال</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($shortcuts as $item)
            <a href="{{ $item['url'] }}"
               class="spotlight-card group relative bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-100 dark:border-gray-700/60 p-3 sm:p-3.5 shadow-xs backdrop-blur-xl transition-all duration-200 hover:shadow-md {{ $item['hover_border'] }} hover:-translate-y-0.5 flex items-center gap-3 cursor-pointer">

                {{-- Colored Icon Box with Soft Vibrant Tint & Border in Dark Mode --}}
                <span
                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3 border hub-box-{{ $item['color'] }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $item['icon'] !!}
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-1.5">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white {{ $item['hover_text'] }} transition-colors truncate">
                            {{ $item['title'] }}
                        </h3>

                        @if(!empty($item['badge']))
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-bold shrink-0 border hub-badge-{{ $item['color'] }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-400 truncate mt-0.5">
                        {{ $item['desc'] }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>
</section>
