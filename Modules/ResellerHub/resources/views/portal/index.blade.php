<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-6 dark:bg-gray-950">

        {{-- Logo and Title --}}
        <div class="mb-8 text-center">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">ورود و ثبت‌ نام در سامانه</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ورود سریع و راه‌اندازی سامانه با شماره موبایل</p>
        </div>

        {{-- Main Card --}}
        <div
            class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-gray-200/50 dark:bg-gray-900 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <livewire:resellerhub.portal.unified-auth :package="request('package')" :period="request('period')"/>
        </div>

        {{-- Footer --}}
        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ date('Y') }} تمامی حقوق محفوظ است.
        </p>

    </div>
</x-guest-layout>
