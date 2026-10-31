@props(['col', 'label'])
@php
    $currentCol = request('sort_by', 'latest');
    $currentOrder = strtolower(request('sort_order', 'desc'));

    if ($currentCol === $col . '_asc') {
        $currentCol = $col;
        $currentOrder = 'asc';
    } elseif ($currentCol === $col . '_desc') {
        $currentCol = $col;
        $currentOrder = 'desc';
    }

    $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
@endphp
<a href="{{ $url }}" class="group inline-flex items-center space-x-1 space-x-reverse cursor-pointer text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
    <span>{{ $label }}</span>
    @if ($currentCol === $col)
        @if ($currentOrder === 'asc')
            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
        @else
            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        @endif
    @else
        <svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-100 transition-opacity inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
    @endif
</a>
