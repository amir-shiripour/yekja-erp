<div class="sticky bottom-4 z-40">
    <div
        class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
        <button type="submit"
                :disabled="submitting || selectedModules.length === 0"
                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 disabled:opacity-50 text-white font-black text-sm shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
            <template x-if="submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ __('resellerhub::messages.packages.form.saving') }}</span>
                        </span>
            </template>
            <template x-if="!submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('resellerhub::messages.packages.form.submit_create') }}</span>
                        </span>
            </template>
        </button>

        <a href="{{ route('reseller.packages.index') }}"
           class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
            {{ __('resellerhub::messages.packages.form.cancel') }}
        </a>
    </div>
</div>
