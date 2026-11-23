@props([
    'name' => null,
    'value' => null,
    'options' => [],
    'model' => null,
    'size' => 'sm',
    'fullWidth' => false,
])

@php
    // Normalize options into uniform array: [['value' => ..., 'label' => ..., 'icon' => ...]]
    $normalizedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $normalizedOptions[] = [
                'value' => (string) ($opt['value'] ?? $key),
                'label' => (string) ($opt['label'] ?? $opt['title'] ?? $key),
                'icon'  => $opt['icon'] ?? null,
                'badge' => $opt['badge'] ?? null,
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $opt,
                'icon'  => null,
                'badge' => null,
            ];
        }
    }

    $wireModel = $attributes->wire('model')->value();
    $initialValue = old($name, $value ?? ($normalizedOptions[0]['value'] ?? ''));

    $sizeClasses = match($size) {
        'xs' => 'px-2 py-1 text-[11px] min-h-[28px]',
        'lg' => 'px-4 py-2 text-sm min-h-[42px]',
        default => 'px-3 py-1.5 text-xs min-h-[34px]',
    };

    $containerWidth = $fullWidth ? 'w-full flex' : 'inline-flex w-full sm:w-auto';
    $buttonWidth = $fullWidth ? 'flex-1' : 'flex-1 sm:flex-initial';
@endphp

<div
    @if(!$model && !$wireModel)
        x-data="{
            selected: @js((string) $initialValue),
            setVal(val) {
                this.selected = val;
                this.$dispatch('input', val);
                this.$dispatch('change', val);
            }
        }"
    @endif
    {{ $attributes->merge(['class' => "items-center p-1 rounded-xl bg-gray-100/90 dark:bg-gray-800/90 border border-gray-200/60 dark:border-gray-700/60 gap-1 select-none {$containerWidth}"]) }}
    role="radiogroup"
>
    @if($name)
        <input
            type="hidden"
            name="{{ $name }}"
            @if($model)
                :value="{{ $model }}"
            @elseif($wireModel)
                wire:model="{{ $wireModel }}"
            @else
                :value="selected"
            @endif
        >
    @endif

    @foreach($normalizedOptions as $opt)
        @php
            $optVal = $opt['value'];
            $optLabel = $opt['label'];
            $optIcon = $opt['icon'];
            $optBadge = $opt['badge'];
        @endphp

        <button
            type="button"
            role="radio"
            @if($model)
                @click="{{ $model }} = '{{ $optVal }}'; $dispatch('change', '{{ $optVal }}')"
            :aria-checked="{{ $model }} === '{{ $optVal }}' ? 'true' : 'false'"
            :class="{{ $model }} === '{{ $optVal }}'
                    ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-xs font-black'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-200/50 dark:hover:bg-gray-700/40 font-bold'"
            @elseif($wireModel)
                wire:click="$set('{{ $wireModel }}', '{{ $optVal }}')"
            :aria-checked="$wire.get('{{ $wireModel }}') === '{{ $optVal }}' ? 'true' : 'false'"
            :class="$wire.get('{{ $wireModel }}') === '{{ $optVal }}'
                    ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-xs font-black'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-200/50 dark:hover:bg-gray-700/40 font-bold'"
            @else
                @click="setVal('{{ $optVal }}')"
            :aria-checked="selected === '{{ $optVal }}' ? 'true' : 'false'"
            :class="selected === '{{ $optVal }}'
                    ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-xs font-black'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-200/50 dark:hover:bg-gray-700/40 font-bold'"
            @endif
            class="relative {{ $buttonWidth }} {{ $sizeClasses }} rounded-lg transition-all duration-150 flex items-center justify-center gap-1.5 cursor-pointer leading-none"
        >
            @if($optIcon)
                <span class="shrink-0 opacity-80">{!! $optIcon !!}</span>
            @endif

            <span class="truncate">{{ $optLabel }}</span>

            @if($optBadge)
                <span
                    class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/40">
                    {{ $optBadge }}
                </span>
            @endif
        </button>
    @endforeach
</div>
