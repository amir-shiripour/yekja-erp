@props(['value'])
<label {{ $attributes->merge(['class' => 'block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1']) }}>
    {{ $value ?? $slot }}
</label>
