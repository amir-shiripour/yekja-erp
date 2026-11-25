@php
    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };
    $itemLabel = $itemLabel ?? 'مورد ثبت‌شده';
@endphp

@if($paginator->total() > 0)
    <div
        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-2xl">
        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
            <div>
                نمایش
                <span class="font-black text-gray-900 dark:text-white">{{ $faNum($paginator->firstItem() ?? 0) }}</span>
                تا
                <span class="font-black text-gray-900 dark:text-white">{{ $faNum($paginator->lastItem() ?? 0) }}</span>
                از مجموع
                <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $faNum($paginator->total()) }}</span>
                {{ $itemLabel }}
            </div>

            <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

            <div class="flex items-center gap-1.5">
                <span class="text-gray-400 text-xs">تعداد در هر صفحه:</span>
                <select name="per_page"
                        aria-label="تعداد در هر صفحه"
                        onchange="window.location.href = this.value"
                        class="py-1 px-2.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer shadow-2xs">
                    @foreach([10, 15, 25, 50, 100] as $size)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                            {{ (int) $paginator->perPage() === $size ? 'selected' : '' }}>
                            {{ $faNum($size) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            @if($paginator->hasPages())
                {{ $paginator->appends(request()->query())->links('themes.admin-pro.partials.pagination') }}
            @else
                <nav role="navigation" aria-label="Pagination Navigation" class="inline-flex items-center gap-1.5">
                    <span
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                    <span
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/25 select-none">
                        {{ $faNum(1) }}
                    </span>
                    <span
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                </nav>
            @endif
        </div>
    </div>
@endif
