@props(['disabled' => false])
@php
    $ariaLabel = $attributes->get('aria-label') ?: ($attributes->get('name') ? ('انتخاب ' . $attributes->get('name')) : 'انتخاب گزینه');
@endphp
<select {{ $disabled ? 'disabled' : '' }} @if(!$attributes->has('aria-label')) aria-label="{{ $ariaLabel }}" @endif {!! $attributes->merge(['class' => 'w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer']) !!}>
    {{ $slot }}
</select>
