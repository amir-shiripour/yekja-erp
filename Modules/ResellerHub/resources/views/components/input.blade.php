@props(['disabled' => false])
@php
    $ariaLabel = $attributes->get('aria-label') ?: ($attributes->get('placeholder') ? null : ($attributes->get('name') ? ('ورودی ' . $attributes->get('name')) : null));
@endphp
<input
    {{ $disabled ? 'disabled' : '' }} @if($ariaLabel && !$attributes->has('aria-label')) aria-label="{{ $ariaLabel }}" @endif {!! $attributes->merge(['class' => 'w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500']) !!}>
