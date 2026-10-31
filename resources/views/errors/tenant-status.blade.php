<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8 text-center border border-gray-100 dark:border-gray-800">
            <div
                class="w-16 h-16 {{ ($type ?? '') === 'pending' ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-500' : (($type ?? '') === 'suspended' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-500' : 'bg-red-50 dark:bg-red-950/50 text-red-500') }} rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $title ?? 'پیام سامانه' }}</h1>
            <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mb-3">{{ $tenant?->name }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                {{ $message ?? '' }}
            </p>
            @if(!empty($tenant?->getBranding('support_phone')))
                <div
                    class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl mb-4 text-xs text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700">
                    تماس با پشتیبانی: <span
                        class="font-bold text-gray-900 dark:text-white font-mono">{{ $tenant->getBranding('support_phone') }}</span>
                </div>
            @endif
            <div class="space-y-3">
                <a href="{{ config('app.url') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition shadow-sm">
                    صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
