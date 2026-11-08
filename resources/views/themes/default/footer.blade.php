<footer class="border-t border-gray-200 dark:border-gray-800/60 bg-gray-50 dark:bg-gray-900/50 pt-16 pb-8 z-10 relative mt-auto">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
        <div class="space-y-4 sm:col-span-2 lg:col-span-1">
            <h3 class="font-black text-xl text-gray-900 dark:text-white">{{ $appName }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                ما به کسب‌وکارها کمک می‌کنیم تا با اتوماسیون فرآیندها و مدیریت هوشمند داده‌ها، با سرعت بیشتری رشد کنند.
            </p>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">محصولات</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="#" class="hover:text-indigo-600 transition-colors">مدیریت مشتریان (CRM)</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">اتوماسیون بازاریابی</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">فرم‌ساز هوشمند</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">شرکت</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="#" class="hover:text-indigo-600 transition-colors">درباره ما</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">فرصت‌های شغلی</a></li>
                <li><a href="#" class="hover:text-indigo-600 transition-colors">ارتباط با ما</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-900 dark:text-white mb-4">تماس با ما</h4>
            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                <li class="dir-ltr text-right sm:text-left lg:text-right">۰۲۱-۹۱۰۰۰۰۰۰</li>
                <li class="dir-ltr text-right sm:text-left lg:text-right">info@domain.com</li>
                <li>تهران، خیابان ولیعصر، پلاک ۱</li>
            </ul>
        </div>
        <div>
            <div class="inline-flex">
                <div class="w-28 h-32 p-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/90 dark:border-gray-700/80 shadow-xs hover:shadow-md transition-all flex flex-col items-center justify-center gap-1.5 text-center relative overflow-hidden group">
                    <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=8588364&Code=zqkZId2ZFv03YFoanALLpEcgCAUvqpve' class="w-full h-full flex flex-col items-center justify-center">
                        <img referrerpolicy='origin'
                             src='https://trustseal.enamad.ir/logo.aspx?id=8588364&Code=zqkZId2ZFv03YFoanALLpEcgCAUvqpve'
                             alt='نماد اعتماد الکترونیکی'
                             style='cursor:pointer'
                             code='zqkZId2ZFv03YFoanALLpEcgCAUvqpve'
                             class="w-16 h-20 object-contain mx-auto"
                             onerror="this.classList.add('hidden'); document.getElementById('enamad-fallback-badge')?.classList.remove('hidden');"
                        >
                        {{-- فال‌بک تصویری اینماد هنگام لود در محیط لوکال/توسعه که سرور اینماد خطای ۴۰۳ می‌دهد --}}
                        <div id="enamad-fallback-badge" class="flex flex-col items-center justify-center text-center">
                            <svg class="w-12 h-12 mb-1 drop-shadow-xs" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="46" fill="#F8FAFC" stroke="#0284C7" stroke-width="3"/>
                                <path d="M50 18 C34 18 24 28 24 42 C24 58 40 70 52 74 C58 76 64 74 68 70 C72 66 74 60 72 54 C70 48 64 45 58 46 C52 47 48 51 46 56 C44 61 46 66 50 67 C44 67 36 60 36 50 C36 40 44 32 54 32 C62 32 68 37 68 44 C68 48 66 52 62 54" stroke="#0369A1" stroke-width="4.5" stroke-linecap="round"/>
                                <circle cx="50" cy="26" r="3.5" fill="#F59E0B"/>
                                <path d="M42 82 L44 78 L46 82 L50 82 L47 84 L48 88 L44 85 L40 88 L41 84 L38 82 Z" fill="#F59E0B"/>
                                <path d="M54 82 L56 78 L58 82 L62 82 L59 84 L60 88 L56 85 L52 88 L53 84 L50 82 Z" fill="#F59E0B"/>
                            </svg>
                            <span class="text-[10px] font-bold text-sky-800 dark:text-sky-300 leading-tight">نماد اعتماد</span>
                            <span class="text-[9px] text-gray-600 dark:text-gray-400 mt-0.5 dir-ltr font-mono">id: 8588364</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="max-w-7xl mx-auto px-6 border-t border-gray-200 dark:border-gray-800 pt-8 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center sm:text-right">
            &copy; {{ date('Y') }} {{ $footerText }}
        </p>
        <div class="flex items-center justify-center gap-4">
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">in</a>
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">tw</a>
            <a href="#" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-indigo-600 hover:text-white transition-colors">ig</a>
        </div>
    </div>
</footer>
