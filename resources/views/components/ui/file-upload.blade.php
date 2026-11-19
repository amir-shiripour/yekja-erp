@props([
    'label' => null,
    'name' => null,
    'accept' => null,
    'multiple' => false,
    'hint' => 'فایل را اینجا بکشید و رها کنید یا کلیک کنید',
    'maxSize' => '۱۰ مگابایت',
])

<div
    x-data="{
        dragging: false,
        uploading: false,
        progress: 0,
        files: [],
        handleFiles(fileList) {
            this.files = Array.from(fileList).map(f => ({
                name: f.name,
                size: this.formatSize(f.size),
                isImage: f.type.startsWith('image/'),
                preview: f.type.startsWith('image/') ? URL.createObjectURL(f) : null,
            }));
        },
        formatSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },
        clearFiles() {
            this.files = [];
            this.$refs.input.value = '';
        }
    }"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-finish="uploading = false; progress = 100"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    class="space-y-2 {{ $attributes->get('class') }}"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <div
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="
            dragging = false;
            if ($event.dataTransfer.files.length) {
                $refs.input.files = $event.dataTransfer.files;
                $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                handleFiles($event.dataTransfer.files);
            }
        "
        @click="$refs.input.click()"
        :class="dragging
            ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/30 scale-[1.01]'
            : 'border-gray-300 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/50 hover:border-indigo-400 hover:bg-indigo-50/20 dark:hover:bg-gray-800/70'"
        class="relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200"
    >
        <input
            x-ref="input"
            type="file"
            class="hidden"
            @if($name) name="{{ $name }}" @endif
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @change="handleFiles($event.target.files)"
            {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'accept', 'multiple', 'hint', 'maxSize']) }}
        >

        <div class="flex flex-col items-center justify-center gap-2">
            <div
                class="w-11 h-11 rounded-2xl bg-indigo-100/80 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center transition-transform"
                :class="dragging ? 'scale-110 -translate-y-1' : ''">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $hint }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500">حداکثر حجم مجاز: {{ $maxSize }}</p>
        </div>

        {{-- Livewire Progress Bar --}}
        <div x-cloak x-show="uploading" class="mt-4 space-y-1.5">
            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 transition-all duration-150 rounded-full"
                     :style="{ width: progress + '%' }"></div>
            </div>
            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 block"
                  x-text="'در حال بارگذاری... ' + progress + '%'"></span>
        </div>
    </div>

    {{-- File Previews --}}
    <template x-if="files.length > 0">
        <div class="space-y-2 pt-1">
            <template x-for="(file, idx) in files" :key="idx">
                <div
                    class="flex items-center justify-between gap-3 p-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <template x-if="file.isImage && file.preview">
                            <img :src="file.preview"
                                 class="w-9 h-9 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shrink-0"
                                 alt="">
                        </template>
                        <template x-if="!file.isImage">
                            <div
                                class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </template>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800 dark:text-gray-200 truncate" x-text="file.name"></p>
                            <p class="text-[11px] text-gray-400 font-mono" dir="ltr" x-text="file.size"></p>
                        </div>
                    </div>
                    <button type="button" @click.stop="clearFiles()"
                            class="p-1 text-gray-400 hover:text-rose-600 rounded-lg transition-colors" title="حذف">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
