<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8 text-center border border-gray-100 dark:border-gray-800">
            <div
                class="w-16 h-16 bg-red-50 dark:bg-red-950/50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">سازمان یا سامانه یافت نشد</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                آدرس سابدامین <span
                    class="font-mono text-indigo-600 dark:text-indigo-400 font-bold dir-ltr inline-block">{{ $subdomain ?? 'مورد نظر' }}</span>
                در پلتفرم ثبت نشده است یا حذف گردیده است.
            </p>
            <div class="space-y-3">
                <a href="{{ config('app.url') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
