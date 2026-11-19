@props([
    'title' => 'موردی برای نمایش یافت نشد',
    'description' => 'در حال حاضر هیچ داده‌ای در این بخش ثبت نشده است.',
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge([
    'class' => 'flex flex-col items-center justify-center text-center py-12 px-6 bg-white/60 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700/80 rounded-3xl space-y-4'
]) }}>
    <div
        class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center shadow-xs">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    <div class="space-y-1 max-w-sm">
        <h3 class="text-sm font-black text-gray-800 dark:text-gray-100">{{ $title }}</h3>
        @if($description)
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $description }}</p>
        @endif
    </div>

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all">
            {{ $actionLabel }}
        </a>
    @elseif(isset($actions))
        <div class="pt-1">
            {{ $actions }}
        </div>
    @endif
</div>
