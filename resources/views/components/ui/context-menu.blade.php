@props([
    'as' => 'div',
])

@php
    $alpineAttributes = '
        x-data="{
            open: false,
            x: 0,
            y: 0,
            touchTimer: null,
            show(e) {
                this.open = true;
                this.$nextTick(() => {
                    const menu = this.$refs.menu;
                    const menuW = menu ? menu.offsetWidth : 210;
                    const menuH = menu ? menu.offsetHeight : 190;
                    const vw = window.innerWidth;
                    const vh = window.innerHeight;

                    let posX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 20);
                    let posY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 20);

                    if (posX + menuW > vw - 16) posX = vw - menuW - 16;
                    if (posX < 16) posX = 16;
                    if (posY + menuH > vh - 16) posY = vh - menuH - 16;
                    if (posY < 16) posY = 16;

                    this.x = posX;
                    this.y = posY;
                });
            },
            touchStart(e) {
                this.touchTimer = setTimeout(() => {
                    this.show(e);
                }, 500);
            },
            touchEnd() {
                clearTimeout(this.touchTimer);
            }
        }"
        @contextmenu.prevent="show($event)"
        @touchstart.passive="touchStart($event)"
        @touchend.passive="touchEnd()"
        @touchmove.passive="touchEnd()"
        @keydown.escape.window="open = false"
        @scroll.window="open = false"
    ';
@endphp

@if($as === 'tr')
    <tr {!! $alpineAttributes !!} {{ $attributes }}>
        {{ $slot }}

        {{-- Floating Context Menu Teleported to Body --}}
        <template x-teleport="body">
            <div
                x-ref="menu"
                x-cloak
                x-show="open"
                @click.outside="open = false"
                @click="open = false"
                x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :style="{ top: y + 'px', left: x + 'px' }"
                class="fixed z-[9999] min-w-[210px] p-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-800 rounded-2xl shadow-2xl text-xs space-y-0.5 select-none"
                style="box-shadow: 0 16px 36px -4px rgba(0,0,0,0.2);"
            >
                {{ $menu ?? $items ?? '' }}
            </div>
        </template>
    </tr>
@else
    <div {!! $alpineAttributes !!} {{ $attributes }}>
        {{ $slot }}

        <template x-teleport="body">
            <div
                x-ref="menu"
                x-cloak
                x-show="open"
                @click.outside="open = false"
                @click="open = false"
                x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :style="{ top: y + 'px', left: x + 'px' }"
                class="fixed z-[9999] min-w-[210px] p-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-800 rounded-2xl shadow-2xl text-xs space-y-0.5 select-none"
                style="box-shadow: 0 16px 36px -4px rgba(0,0,0,0.2);"
            >
                {{ $menu ?? $items ?? '' }}
            </div>
        </template>
    </div>
@endif
