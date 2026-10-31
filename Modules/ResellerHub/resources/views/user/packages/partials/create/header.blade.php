<div
    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
    <div class="flex items-center gap-3">
        <a href="{{ route('reseller.packages.index') }}"
           class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer"
           title="{{ __('resellerhub::messages.packages.form.back_to_list') }}">
            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                <span>{{ __('resellerhub::messages.packages.form.create_title') }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('resellerhub::messages.packages.form.create_desc') }}
            </p>
        </div>
    </div>

    <a href="{{ route('reseller.packages.index') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
        <span>{{ __('resellerhub::messages.packages.form.cancel_and_back') }}</span>
    </a>
</div>

@if (isset($errors) && $errors->any())
    <div
        class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 text-xs sm:text-sm space-y-1">
        <div class="font-bold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ __('resellerhub::messages.packages.form.error_create_header') }}</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 ms-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
