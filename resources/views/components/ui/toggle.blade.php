@props([
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'size' => 'md',
    'color' => 'indigo',
])

@php
    $trackSize = match($size) {
        'sm' => 'w-9 h-5 after:h-4 after:w-4',
        default => 'w-11 h-6 after:h-5 after:w-5',
    };

    $checkedColor = match($color) {
        'emerald', 'success' => 'peer-checked:bg-emerald-600 dark:peer-checked:bg-emerald-500',
        'rose', 'danger'     => 'peer-checked:bg-rose-600 dark:peer-checked:bg-rose-500',
        'amber', 'warning'   => 'peer-checked:bg-amber-500 dark:peer-checked:bg-amber-500',
        default              => 'peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500',
    };
@endphp

<label
    class="relative inline-flex items-center {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} shrink-0 select-none">
    <input
        type="checkbox"
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        @if($checked) checked @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'sr-only peer']) }}
    >
    <div
        class="{{ $trackSize }} {{ $checkedColor }} bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500/25 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 dark:after:border-gray-600 after:border after:rounded-full after:transition-all transition-colors shadow-2xs"></div>
</label>
