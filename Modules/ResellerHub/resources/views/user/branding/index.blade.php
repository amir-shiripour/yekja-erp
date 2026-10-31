@extends('layouts.user')

@section('title', 'تنظیمات برند اختصاصی (White-label)')

@section('content')
<div x-data="brandingManager({
        logoUrl: '{{ $reseller->brand_logo ? asset('storage/' . $reseller->brand_logo) : '' }}',
        faviconUrl: '{{ $reseller->brand_favicon ? asset('storage/' . $reseller->brand_favicon) : '' }}'
     })"
     class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

    @include('resellerhub::partials.toast')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </span>
                <span>تنظیمات برند و وایت‌لیبل (White-label)</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                اطلاعات، لوگو و کپی‌رایت خود را تنظیم کنید تا تمام CRMهای مشتریان با نام تجاری شما نمایش داده شوند.
            </p>
        </div>

        <a href="{{ route('reseller.dashboard') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>بازگشت به داشبورد</span>
        </a>
    </div>

    <div
        class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-5 flex items-start gap-4 shadow-2xs">
        <span class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-400 rounded-xl shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <div class="text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 leading-relaxed">
            <strong class="font-bold">قابلیت برند اختصاصی (White-label):</strong>
            با تنظیم لوگو، نام و مشخصات زیر، هنگام ورود مشتریان به سابدامین اختصاصی‌شان، تمامی عنوان‌ها، فوترها، لوگوی
            بالای منو و شماره‌های پشتیبانی با مشخصات شرکت شما جایگزین شده و هیچ اسمی از پلتفرم مادر دیده نخواهد شد.
        </div>
    </div>

    <form @submit.prevent="saveBranding($el)"
          enctype="multipart/form-data"
          class="bg-white dark:bg-gray-800/80 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60 overflow-hidden backdrop-blur-xl">
        @csrf

        <div class="p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">مشخصات تجاری نماینده</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                        نام رسمی شرکت / برند نماینده <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name', $reseller->company_name) }}"
                           required placeholder="مثال: شرکت فناوری‌های پزشکی پارس"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 transition">
                    <template x-if="errors.company_name">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.company_name[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                        عنوان نمایشی در هدر و صفحه لاگین (Brand Title)
                    </label>
                    <input type="text" name="brand_title" x-model="brandTitle" value="{{ old('brand_title', $reseller->brand_title) }}"
                           placeholder="مثال: سامانه هوشمند مدیریت کلینیک پارس"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 transition">
                    <template x-if="errors.brand_title">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.brand_title[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                        شماره تماس پشتیبانی نماینده
                    </label>
                    <input type="text" name="support_phone" value="{{ old('support_phone', $reseller->support_phone) }}"
                           placeholder="۰۲۱۸۸۸۸۸۸۸۸"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-left dir-ltr focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 transition">
                    <template x-if="errors.support_phone">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.support_phone[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                        ایمیل پشتیبانی نماینده
                    </label>
                    <input type="email" name="support_email"
                           value="{{ old('support_email', $reseller->support_email) }}"
                           placeholder="support@yourcompany.ir"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-left dir-ltr focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 transition">
                    <template x-if="errors.support_email">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.support_email[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                    متن کپی‌رایت فوتر پنل مشتریان
                </label>
                <input type="text" name="brand_footer_text"
                       value="{{ old('brand_footer_text', $reseller->brand_footer_text) }}"
                       placeholder="تمامی حقوق محفوظ است © شرکت فناوری پارس"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 transition">
                <template x-if="errors.brand_footer_text">
                    <p class="text-xs text-red-500 mt-1" x-text="errors.brand_footer_text[0]"></p>
                </template>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <span class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">لوگو و نماد اختصاصی (وایت‌لیبل)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تصاویر برند خود را بارگذاری کنید تا در هدر پنل‌ها، سابدامین اختصاصی و تب مرورگر مشتریان جایگزین شوند</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Zone 1: Brand Logo --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                            لوگوی اختصاصی برند
                        </label>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                            انواع فرمت‌های تصویری
                        </span>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="file"
                           x-ref="logoInput"
                           name="brand_logo"
                           accept="image/*,.ico"
                           class="hidden"
                           @change="handleFileSelect($event, 'logo')">
                    <input type="hidden" name="remove_brand_logo" :value="removeLogoFlag ? 1 : 0">

                    <div class="flex items-center gap-3">
                        <!-- Drag & Drop / Select button area (matches settings module style) -->
                        <div class="relative flex-1"
                             @dragover.prevent="isDraggingLogo = true"
                             @dragleave.prevent="isDraggingLogo = false"
                             @drop.prevent="handleDrop($event, 'logo')">
                            <label @click="$refs.logoInput.click()"
                                   :class="isDraggingLogo ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-900/50'"
                                   class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                                <svg class="w-5 h-5 ml-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate" x-text="logoFileName ? (logoFileName + ' (' + logoFileSize + ')') : 'انتخاب یا رها کردن فایل لوگو...'"></span>
                            </label>
                        </div>

                        <!-- Minimal Preview Box (matches settings module w-12 h-12) -->
                        <div class="relative group shrink-0">
                            <div class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xs"
                                 style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 6px 6px;">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="پیش‌نمایش لوگو" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!logoPreview">
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <template x-if="logoPreview">
                                <button type="button"
                                        @click.stop="removeFile('logo')"
                                        title="حذف لوگو"
                                        class="absolute -top-1.5 -end-1.5 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer transition-transform hover:scale-110">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
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
                        <p class="text-xs text-rose-500 mt-1.5 ms-1 font-medium" x-text="errors.brand_logo[0]"></p>
                    </template>
                </div>

                {{-- Zone 2: Brand Favicon --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                            نماد تب مرورگر (فاوآیکون)
                        </label>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                            انواع فرمت‌های آیکون و تصویر
                        </span>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="file"
                           x-ref="faviconInput"
                           name="brand_favicon"
                           accept="image/*,.ico"
                           class="hidden"
                           @change="handleFileSelect($event, 'favicon')">
                    <input type="hidden" name="remove_brand_favicon" :value="removeFaviconFlag ? 1 : 0">

                    <div class="flex items-center gap-3">
                        <!-- Drag & Drop / Select button area (matches settings module style) -->
                        <div class="relative flex-1"
                             @dragover.prevent="isDraggingFavicon = true"
                             @dragleave.prevent="isDraggingFavicon = false"
                             @drop.prevent="handleDrop($event, 'favicon')">
                            <label @click="$refs.faviconInput.click()"
                                   :class="isDraggingFavicon ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-900/50'"
                                   class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                                <svg class="w-5 h-5 ml-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <span class="truncate" x-text="faviconFileName ? (faviconFileName + ' (' + faviconFileSize + ')') : 'انتخاب یا رها کردن فایل فاوآیکون...'"></span>
                            </label>
                        </div>

                        <!-- Minimal Preview Box (matches settings module w-12 h-12) -->
                        <div class="relative group shrink-0">
                            <div class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xs"
                                 style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 6px 6px;">
                                <template x-if="faviconPreview">
                                    <img :src="faviconPreview" alt="پیش‌نمایش نماد" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!faviconPreview">
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                </template>
                            </div>
                            <template x-if="faviconPreview">
                                <button type="button"
                                        @click.stop="removeFile('favicon')"
                                        title="حذف فاوآیکون"
                                        class="absolute -top-1.5 -end-1.5 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer transition-transform hover:scale-110">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
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
                        <p class="text-xs text-rose-500 mt-1.5 ms-1 font-medium" x-text="errors.brand_favicon[0]"></p>
                    </template>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-gray-50 dark:bg-gray-900/40 flex items-center justify-end">
            <button type="submit"
                    :disabled="saving"
                    class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-indigo-600/25 flex items-center gap-2">
                <template x-if="saving">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>در حال ذخیره‌سازی...</span>
                    </span>
                </template>
                <template x-if="!saving">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ذخیره تنظیمات برندینگ</span>
                    </span>
                </template>
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        function brandingManager(config) {
            return {
                logoPreview: config.logoUrl || null,
                faviconPreview: config.faviconUrl || null,
                isDraggingLogo: false,
                isDraggingFavicon: false,
                logoFileName: '',
                logoFileSize: '',
                faviconFileName: '',
                faviconFileSize: '',
                removeLogoFlag: false,
                removeFaviconFlag: false,
                brandTitle: @json($reseller->brand_title ?: $reseller->company_name ?: 'سامانه اختصاصی'),
                saving: false,
                errors: {},
                toast: {show: false, message: '', type: 'success'},

                faNum(str) {
                    if (str === null || str === undefined) return '';
                    return String(str).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                },

                formatFileSize(bytes) {
                    if (!bytes || bytes === 0) return '۰ بایت';
                    const k = 1024;
                    const sizes = ['بایت', 'کیلوبایت', 'مگابایت'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return this.faNum(parseFloat((bytes / Math.pow(k, i)).toFixed(1))) + ' ' + sizes[i];
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                handleFileSelect(event, target) {
                    const file = event.target.files ? event.target.files[0] : null;
                    if (file) {
                        this.processFile(file, target);
                    }
                },

                handleDrop(event, target) {
                    if (target === 'logo') this.isDraggingLogo = false;
                    if (target === 'favicon') this.isDraggingFavicon = false;

                    const files = event.dataTransfer ? event.dataTransfer.files : null;
                    if (!files || files.length === 0) return;

                    const file = files[0];
                    const inputRef = target === 'logo' ? this.$refs.logoInput : this.$refs.faviconInput;
                    if (inputRef) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        inputRef.files = dt.files;
                    }
                    this.processFile(file, target);
                },

                processFile(file, target) {
                    if (target === 'logo') {
                        this.logoFileName = file.name;
                        this.logoFileSize = this.formatFileSize(file.size);
                        this.removeLogoFlag = false;
                    } else {
                        this.faviconFileName = file.name;
                        this.faviconFileSize = this.formatFileSize(file.size);
                        this.removeFaviconFlag = false;
                    }
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (target === 'logo') {
                            this.logoPreview = e.target.result;
                        } else if (target === 'favicon') {
                            this.faviconPreview = e.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                },

                removeFile(target) {
                    if (target === 'logo') {
                        this.logoPreview = null;
                        this.logoFileName = '';
                        this.logoFileSize = '';
                        this.removeLogoFlag = true;
                        if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                    } else {
                        this.faviconPreview = null;
                        this.faviconFileName = '';
                        this.faviconFileSize = '';
                        this.removeFaviconFlag = true;
                        if (this.$refs.faviconInput) this.$refs.faviconInput.value = '';
                    }
                },

                previewImage(event, target) {
                    this.handleFileSelect(event, target);
                },

                async saveBranding(formEl) {
                    if (this.saving) return;
                    this.saving = true;
                    this.errors = {};

                    const formData = new FormData(formEl);

                    try {
                        const res = await fetch(`{{ route('reseller.branding.update') }}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.logoPreview = data.brand_logo_url || null;
                            this.faviconPreview = data.brand_favicon_url || null;
                            this.removeLogoFlag = false;
                            this.removeFaviconFlag = false;
                            this.logoFileName = '';
                            this.faviconFileName = '';
                            this.showToast(data.message, 'success');
                        } else if (res.status === 422) {
                            this.errors = data.errors || {};
                            this.showToast('لطفاً خطاهای فرم را بررسی کنید.', 'error');
                        } else {
                            this.showToast(data.message || 'خطا در ذخیره تنظیمات.', 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                    } finally {
                        this.saving = false;
                    }
                }
            };
        }
    </script>
@endpush
@endsection
