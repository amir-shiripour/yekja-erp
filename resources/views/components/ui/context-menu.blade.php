<div
    x-data="{
        open: false,
        x: 0,
        y: 0,
        show(e) {
            this.open = true;
            this.$nextTick(() => {
                const menu = this.$refs.menu;
                const menuW = menu ? menu.offsetWidth : 200;
                const menuH = menu ? menu.offsetHeight : 180;
                const vw = window.innerWidth;
                const vh = window.innerHeight;

                let posX = e.clientX;
                let posY = e.clientY;

                if (posX + menuW > vw - 12) posX = vw - menuW - 12;
                if (posX < 12) posX = 12;
                if (posY + menuH > vh - 12) posY = vh - menuH - 12;
                if (posY < 12) posY = 12;

                this.x = posX;
                this.y = posY;
            });
        }
    }"
    @contextmenu.prevent="show($event)"
    @keydown.escape.window="open = false"
    @scroll.window="open = false"
    {{ $attributes }}
>
    {{-- Trigger area (default slot) --}}
    {{ $slot }}

    {{-- Floating Context Menu --}}
    <template x-teleport="body">
        <div
            x-ref="menu"
            x-cloak
            x-show="open"
            @click.outside="open = false"
            @click="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :style="{ top: y + 'px', left: x + 'px' }"
            class="fixed z-[9999] min-w-[190px] p-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700/80 rounded-2xl shadow-2xl text-xs space-y-0.5"
        >
            {{ $items ?? '' }}
        </div>
    </template>
</div>
