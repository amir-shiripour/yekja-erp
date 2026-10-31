<div role="tabpanel" id="tabpanel-branding" aria-labelledby="tab-branding" x-show="activeTab === 'branding'" x-cloak class="space-y-6">
    <div
        class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-5 flex items-start gap-4 shadow-2xs">
        <span
            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-400 rounded-xl shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
        <div class="text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 leading-relaxed">
            <strong class="font-bold">قابلیت برند اختصاصی (White-label):</strong>
            با تنظیم لوگو، نام و مشخصات زیر، هنگام ورود مشتریان به سابدامین اختصاصی‌شان، تمامی عنوان‌ها،
            فوترها،
            لوگوی بالای منو و شماره‌های پشتیبانی با مشخصات شرکت شما جایگزین شده و هیچ اسمی از پلتفرم مادر
            دیده
            نخواهد شد.
        </div>
    </div>

    <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span
                class="p-2 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">مشخصات تجاری و برند
                    نماینده</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نام، عنوان و اطلاعات تماسی که در
                    پنل
                    اختصاصی مشتریان نمایش داده می‌شود</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-resellerhub::label>
                    نام رسمی شرکت / برند نماینده <span class="text-red-500 font-bold">*</span>
                </x-resellerhub::label>
                <input type="text" name="company_name" x-model="companyName"
                       value="{{ old('company_name', $reseller->company_name) }}"
                       placeholder="مثال: شرکت فناوری‌های پزشکی پارس"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500">
                <template x-if="errors.company_name">
                    <p class="text-xs text-red-500 mt-1 ms-1" x-text="errors.company_name[0]"></p>
                </template>
            </div>

            <div>
                <x-resellerhub::label>
                    عنوان نمایشی در هدر و صفحه لاگین (Brand Title)
                </x-resellerhub::label>
                <input type="text" name="brand_title" x-model="brandTitle"
                       value="{{ old('brand_title', $reseller->brand_title) }}"
                       placeholder="مثال: سامانه هوشمند مدیریت کلینیک پارس"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500">
                <template x-if="errors.brand_title">
                    <p class="text-xs text-red-500 mt-1 ms-1" x-text="errors.brand_title[0]"></p>
                </template>
            </div>

            <div>
                <x-resellerhub::label>
                    شماره تماس پشتیبانی نماینده
                </x-resellerhub::label>
                <input type="text" name="support_phone" x-model="supportPhone"
                       value="{{ old('support_phone', $reseller->support_phone) }}"
                       placeholder="۰۲۱۸۸۸۸۸۸۸۸"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 text-left dir-ltr">
                <template x-if="errors.support_phone">
                    <p class="text-xs text-red-500 mt-1 ms-1" x-text="errors.support_phone[0]"></p>
                </template>
            </div>

            <div>
                <x-resellerhub::label>
                    ایمیل پشتیبانی نماینده
                </x-resellerhub::label>
                <input type="email" name="support_email"
                       value="{{ old('support_email', $reseller->support_email) }}"
                       placeholder="support@yourcompany.ir"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 text-left dir-ltr">
                <template x-if="errors.support_email">
                    <p class="text-xs text-red-500 mt-1 ms-1" x-text="errors.support_email[0]"></p>
                </template>
            </div>
        </div>

        <div>
            <x-resellerhub::label>
                متن کپی‌رایت فوتر پنل مشتریان
            </x-resellerhub::label>
            <input type="text" name="brand_footer_text" x-model="brandFooterText"
                   value="{{ old('brand_footer_text', $reseller->brand_footer_text) }}"
                   placeholder="تمامی حقوق محفوظ است © شرکت فناوری پارس"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500">
            <template x-if="errors.brand_footer_text">
                <p class="text-xs text-red-500 mt-1 ms-1" x-text="errors.brand_footer_text[0]"></p>
            </template>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span
                class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">لوگو و نماد اختصاصی
                    (وایت‌لیبل)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تصاویر برند خود را بارگذاری کنید
                    تا
                    در هدر پنل‌ها، سابدامین اختصاصی و تب مرورگر مشتریان جایگزین شوند</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                        لوگوی اختصاصی برند
                    </label>
                    <span
                        class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                انواع فرمت‌های تصویری
                            </span>
                </div>

                <input type="file"
                       x-ref="logoInput"
                       name="brand_logo"
                       accept="image/*,.ico"
                       class="hidden"
                       @change="handleFileSelect($event, 'logo')">
                <input type="hidden" name="remove_brand_logo" :value="removeLogoFlag ? 1 : 0">

                <div class="flex items-center gap-3">
                    <div class="relative flex-1"
                         @dragover.prevent="isDraggingLogo = true"
                         @dragleave.prevent="isDraggingLogo = false"
                         @drop.prevent="handleDrop($event, 'logo')">
                        <label @click="$refs.logoInput.click()"
                               :class="isDraggingLogo ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-900/50'"
                               class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                            <svg class="w-5 h-5 ml-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate"
                                  x-text="logoFileName ? (logoFileName + ' (' + logoFileSize + ')') : 'انتخاب یا رها کردن فایل لوگو...'"></span>
                        </label>
                    </div>
                    <div class="relative group shrink-0">
                        <div
                            class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xs"
                            style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 6px 6px;">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="پیش‌نمایش لوگو"
                                     class="max-w-full max-h-full object-contain">
                            </template>
                            <template x-if="!logoPreview">
                                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </template>
                        </div>
                        <template x-if="logoPreview">
                            <button type="button"
                                    @click.stop="removeFile('logo')"
                                    title="حذف لوگو"
                                    class="absolute -top-1.5 -end-1.5 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer transition-transform hover:scale-110">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-1.5 px-1">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        حداکثر ۲ مگابایت • تبدیل خودکار به فرمت بهینه WebP
                    </p>
                    <template x-if="logoPreview">
                        <button type="button"
                                @click="removeFile('logo')"
                                class="text-xs text-rose-500 hover:text-rose-600 font-medium hover:underline cursor-pointer">
                            حذف لوگو
                        </button>
                    </template>
                </div>

                <template x-if="errors.brand_logo">
                    <p class="text-xs text-rose-500 mt-1.5 ms-1 font-medium"
                       x-text="errors.brand_logo[0]"></p>
                </template>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                        نماد تب مرورگر (فاوآیکون)
                    </label>
                    <span
                        class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                انواع فرمت‌های آیکون و تصویر
                            </span>
                </div>

                <input type="file"
                       x-ref="faviconInput"
                       name="brand_favicon"
                       accept="image/*,.ico"
                       class="hidden"
                       @change="handleFileSelect($event, 'favicon')">
                <input type="hidden" name="remove_brand_favicon" :value="removeFaviconFlag ? 1 : 0">

                <div class="flex items-center gap-3">
                    <div class="relative flex-1"
                         @dragover.prevent="isDraggingFavicon = true"
                         @dragleave.prevent="isDraggingFavicon = false"
                         @drop.prevent="handleDrop($event, 'favicon')">
                        <label @click="$refs.faviconInput.click()"
                               :class="isDraggingFavicon ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-900/50'"
                               class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                            <svg class="w-5 h-5 ml-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            <span class="truncate"
                                  x-text="faviconFileName ? (faviconFileName + ' (' + faviconFileSize + ')') : 'انتخاب یا رها کردن فایل فاوآیکون...'"></span>
                        </label>
                    </div>

                    <div class="relative group shrink-0">
                        <div
                            class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xs"
                            style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 6px 6px;">
                            <template x-if="faviconPreview">
                                <img :src="faviconPreview" alt="پیش‌نمایش نماد"
                                     class="max-w-full max-h-full object-contain">
                            </template>
                            <template x-if="!faviconPreview">
                                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </template>
                        </div>
                        <template x-if="faviconPreview">
                            <button type="button"
                                    @click.stop="removeFile('favicon')"
                                    title="حذف فاوآیکون"
                                    class="absolute -top-1.5 -end-1.5 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer transition-transform hover:scale-110">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-1.5 px-1">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        حداکثر ۱ مگابایت • تبدیل خودکار به فرمت بهینه WebP
                    </p>
                    <template x-if="faviconPreview">
                        <button type="button"
                                @click="removeFile('favicon')"
                                class="text-xs text-rose-500 hover:text-rose-600 font-medium hover:underline cursor-pointer">
                            حذف فاوآیکون
                        </button>
                    </template>
                </div>

                <template x-if="errors.brand_favicon">
                    <p class="text-xs text-rose-500 mt-1.5 ms-1 font-medium"
                       x-text="errors.brand_favicon[0]"></p>
                </template>
            </div>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card class="p-6 sm:p-8 space-y-8">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span class="p-2.5 bg-pink-50 dark:bg-pink-950/60 text-pink-600 dark:text-pink-400 rounded-2xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5V5a2 2 0 114 0v7.5A5 5 0 0113 17a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">رنگ سازمانی و پیش‌نمایش زنده
                    CRM (Live Mockup)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">رنگ اصلی برند خود را برگزینید و
                    پیش‌نمایش زنده پنل مشتریان را به صورت آنی مشاهده فرمایید</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <x-resellerhub::label>
                        رنگ سازمانی اصلی (Primary Color) <span class="text-indigo-500 font-bold">*</span>
                    </x-resellerhub::label>
                    <p class="text-xs text-gray-400 mb-3">این رنگ بر روی دکمه‌های اصلی، هدر، المان‌های فعال
                        و سایدبار پنل‌های مشتریان اعمال می‌شود.</p>

                    <div class="flex items-center gap-3">
                        <div
                            class="relative w-12 h-11 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-2xs shrink-0 cursor-pointer">
                            <input type="color" x-model="brandPrimaryColor" aria-label="انتخاب رنگ سازمانی"
                                   class="absolute -inset-2 w-16 h-16 cursor-pointer opacity-0">
                            <div class="w-full h-full rounded-xl transition-transform"
                                 :style="'background-color: ' + brandPrimaryColor"></div>
                        </div>
                        <div class="relative flex-1">
                            <x-resellerhub::input class="text-left font-mono font-bold" type="text"
                                                  x-model="brandPrimaryColor"
                                                  name="brand_primary_color"
                                                  dir="ltr"
                                                  placeholder="#4f46e5"
                            />
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">پالت رنگ‌های محبوب و استاندارد</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="preset in colorPresets" :key="preset.hex">
                            <button type="button" @click="brandPrimaryColor = preset.hex"
                                    :class="brandPrimaryColor.toLowerCase() === preset.hex.toLowerCase() ? 'ring-2 ring-indigo-500 dark:ring-indigo-400 border-transparent shadow-xs' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="flex items-center gap-2 p-2 rounded-xl border bg-white dark:bg-gray-800 text-right transition cursor-pointer group">
                                <span
                                    class="w-4 h-4 rounded-full shrink-0 shadow-2xs transition-transform group-hover:scale-110"
                                    :style="'background-color: ' + preset.hex"></span>
                                <span
                                    class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate"
                                    x-text="preset.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div
                    class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-900/90 text-white shadow-xl overflow-hidden">
                    <div
                        class="px-4 py-3 bg-gray-800/90 border-b border-gray-700/80 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div
                            class="flex-1 max-w-xs mx-auto bg-gray-900/80 rounded-lg px-3 py-1 text-xs font-mono text-gray-400 text-center truncate border border-gray-700/60 dir-ltr">
                            https://crm.customer-domain.ir
                        </div>
                        <span
                            class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800/50">پیش‌نمایش زنده</span>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 p-4 min-h-[300px] flex gap-3 text-xs">
                        <div
                            class="w-36 sm:w-44 bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-200/80 dark:border-gray-700/80 space-y-3 shrink-0 flex flex-col justify-between shadow-2xs">
                            <div class="space-y-3">
                                <div
                                    class="flex items-center gap-2 pb-2.5 border-b border-gray-100 dark:border-gray-700">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview"
                                             class="w-6 h-6 object-contain rounded shrink-0">
                                    </template>
                                    <template x-if="!logoPreview">
                                                    <span
                                                        class="w-6 h-6 rounded flex items-center justify-center font-black text-white text-xs shrink-0"
                                                        :style="'background-color: ' + brandPrimaryColor">
                                                        <span x-text="(brandTitle || 'P').charAt(0)"></span>
                                                    </span>
                                    </template>
                                    <span
                                        class="font-black text-xs text-gray-900 dark:text-white truncate"
                                        x-text="brandTitle || 'سامانه مدیریت'"></span>
                                </div>

                                <div class="space-y-1">
                                    <div
                                        class="px-2.5 py-1.5 rounded-lg font-bold text-xs text-white flex items-center gap-2 shadow-2xs"
                                        :style="'background-color: ' + brandPrimaryColor">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        <span>پیشخوان</span>
                                    </div>
                                    <div
                                        class="px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>پرونده‌ها</span>
                                    </div>
                                    <div
                                        class="px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>نوبت‌دهی</span>
                                    </div>
                                    <div
                                        class="px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span>گزارش‌ها</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700 text-xs text-gray-400 truncate">
                                پشتیبانی: <span class="text-gray-700 dark:text-gray-200 font-bold"
                                                x-text="supportPhone ? faNum(supportPhone) : '۰۲۱...'"></span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl p-2.5 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between gap-2 shadow-2xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-gray-900 dark:text-white text-xs"
                                          x-text="companyName || 'کلینیک'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-white px-2.5 py-1 rounded-lg font-bold shadow-2xs"
                                        :style="'background-color: ' + brandPrimaryColor">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span>ثبت نوبت</span>
                                                </span>
                                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div
                                    class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                    <span class="text-xs text-gray-400 block">بیماران امروز</span>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-bold text-gray-900 dark:text-white text-xs">۲۴ نفر</span>
                                        <span class="w-2 h-2 rounded-full"
                                              :style="'background-color: ' + brandPrimaryColor"></span>
                                    </div>
                                </div>
                                <div
                                    class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                    <span class="text-xs text-gray-400 block">گردش مالی روز</span>
                                    <div class="flex items-center justify-between">
                                                    <span
                                                        class="font-bold text-gray-900 dark:text-white text-xs">۸.۵ م</span>
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="pt-2 text-center text-xs text-gray-400 border-t border-gray-200/60 dark:border-gray-800 truncate"
                                x-text="brandFooterText || 'تمامی حقوق محفوظ است'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-resellerhub::card>
    <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span
                class="p-2.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">تعرفه‌گذاری ماژول برند
                    اختصاصی و وایت‌لیبل CRM</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین هزینه فعال‌سازی و اشتراک
                    برای مشتریانی که خواهان درج لوگو، رنگ و عنوان اختصاصی خود هستند</p>
            </div>
        </div>

        <div
            class="p-4 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-2xl text-xs text-emerald-900 dark:text-emerald-300 leading-relaxed">
            چنانچه مایلید قابلیت قرار دادن هویت بصری و لوگوی اختصاصی کلینیک را به عنوان یک سرویس ویژه به
            مشتریان بفروشید، مبالغ زیر را تعیین فرمایید. در صورت خالی یا صفر بودن، این سرویس به صورت رایگان
            برای مشتریان اعمال خواهد شد.
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    تعرفه اشتراک سالانه وایت‌لیبل ({{ $currencyLabel }})
                </label>
                <div
                    class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                    <input type="text"
                           x-model="whiteLabelYearlyDisplay"
                           @input="whiteLabelYearlyPrice = $event.target.value.replace(/[^0-9]/g, ''); whiteLabelYearlyDisplay = whiteLabelYearlyPrice ? Number(whiteLabelYearlyPrice).toLocaleString('en-US') : ''"
                           placeholder="۰"
                           class="w-full px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                    <input type="hidden" name="white_label_yearly_price" :value="whiteLabelYearlyPrice">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    تعرفه اشتراک ماهانه وایت‌لیبل ({{ $currencyLabel }})
                </label>
                <div
                    class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                    <input type="text"
                           x-model="whiteLabelMonthlyDisplay"
                           @input="whiteLabelMonthlyPrice = $event.target.value.replace(/[^0-9]/g, ''); whiteLabelMonthlyDisplay = whiteLabelMonthlyPrice ? Number(whiteLabelMonthlyPrice).toLocaleString('en-US') : ''"
                           placeholder="۰"
                           class="w-full px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                    <input type="hidden" name="white_label_monthly_price" :value="whiteLabelMonthlyPrice">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    هزینه راه‌اندازی و اتصال اولیه ({{ $currencyLabel }})
                </label>
                <div
                    class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                    <input type="text"
                           x-model="whiteLabelSetupDisplay"
                           @input="whiteLabelSetupFee = $event.target.value.replace(/[^0-9]/g, ''); whiteLabelSetupDisplay = whiteLabelSetupFee ? Number(whiteLabelSetupFee).toLocaleString('en-US') : ''"
                           placeholder="۰"
                           class="w-full px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                    <input type="hidden" name="white_label_setup_fee" :value="whiteLabelSetupFee">
                </div>
            </div>
        </div>
    </x-resellerhub::card>
</div>
