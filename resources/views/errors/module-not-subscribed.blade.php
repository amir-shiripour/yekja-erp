<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8 text-center border border-gray-100 dark:border-gray-800">
            <div
                class="w-16 h-16 bg-amber-50 dark:bg-amber-950/50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">ماژول غیرفعال است</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                دسترسی به بخش <span
                    class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $moduleSlug }}</span> در پلن
                خریداری‌شده شما فعال نیست. جهت ارتقای پکیج و فعال‌سازی این امکان با پشتیبانی در تماس باشید.
            </p>
            <div class="space-y-3">
                <a href="{{ route('user.dashboard') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                    بازگشت به پیشخوان
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
