<?php

namespace Modules\ResellerHub\App\Enums;

use Modules\ResellerHub\App\Helpers\ResellerHelper;

/**
 * Modern PHP 8.2+ Backed Enum for all quota-tracked capabilities across ResellerHub.
 * Single Source of Truth (SSOT) for keys, titles, descriptions, units, icons, modules, and guarded routes.
 */
enum QuotaKey: string
{
    // Numeric Resource Quotas
    case Invoices = 'invoices';
    case Clients = 'clients';
    case Appointments = 'appointments';
    case BookingProviders = 'booking_providers';
    case Products = 'products';
    case Warehouses = 'warehouses';
    case Stores = 'stores';
    case Orders = 'orders';

    // Boolean Feature Capabilities
    case MultiVendor = 'multi_vendor';
    case WmsEnabled = 'wms_enabled';
    case Coupons = 'coupons';
    case OnlineBooking = 'online_booking';
    case OnlinePayment = 'online_payment';
    case SmsReminders = 'sms_reminders';
    case WaitingList = 'waiting_list';
    case ClientPortal = 'client_portal';
    case ClientImportExport = 'client_import_export';
    case ClientWallet = 'client_wallet';

    public function isFeature(): bool
    {
        return match ($this) {
            self::MultiVendor,
            self::WmsEnabled,
            self::Coupons,
            self::OnlineBooking,
            self::OnlinePayment,
            self::SmsReminders,
            self::WaitingList,
            self::ClientPortal,
            self::ClientImportExport,
            self::ClientWallet => true,
            default => false,
        };
    }

    public function isNumeric(): bool
    {
        return !$this->isFeature();
    }

    public function shortTitle(): string
    {
        return match ($this) {
            self::Invoices => 'فاکتورها',
            self::Clients => 'مشتریان و پرونده‌ها',
            self::Appointments => 'نوبت‌ها',
            self::BookingProviders => 'پزشکان و ارائه‌دهندگان',
            self::Products => 'محصولات',
            self::Warehouses => 'انبارها',
            self::Stores => 'شعب و فروشگاه‌ها',
            self::Orders => 'سفارش‌ها',
            self::OnlineBooking => 'نوبت‌دهی آنلاین',
            self::OnlinePayment => 'پرداخت آنلاین',
            self::SmsReminders => 'یادآور پیامکی',
            self::WaitingList => 'صف انتظار',
            self::ClientPortal => 'پرتال بیمار/مشتری',
            self::ClientImportExport => 'ورود/خروج پرونده‌ها',
            self::ClientWallet => 'کیف پول',
            self::MultiVendor => 'چندفروشگاهی',
            self::WmsEnabled => 'انبارداری پیشرفته',
            self::Coupons => 'کدهای تخفیف',
        };
    }

    public function formatLimit(?int $limit): string
    {
        if ($limit === null || $limit === -1) {
            return 'نامحدود';
        }

        $unitStr = $this->unit() ? (' ' . $this->unit()) : '';
        return ResellerHelper::toPersianDigits(number_format($limit)) . $unitStr;
    }

    public function styling(): array
    {
        return match ($this) {
            self::Clients => [
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/40',
                'accent' => 'emerald',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
            ],
            self::Appointments => [
                'bg' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-800/40',
                'accent' => 'indigo',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            ],
            self::BookingProviders => [
                'bg' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border-teal-100 dark:border-teal-800/40',
                'accent' => 'teal',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zm-1-4h2m-1-1v2"/>',
            ],
            self::Invoices => [
                'bg' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800/40',
                'accent' => 'amber',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>',
            ],
            self::Products => [
                'bg' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/40',
                'accent' => 'blue',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            ],
            self::Warehouses => [
                'bg' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-800/40',
                'accent' => 'purple',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
            ],
            self::Stores => [
                'bg' => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border-cyan-100 dark:border-cyan-800/40',
                'accent' => 'cyan',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            ],
            self::Orders => [
                'bg' => 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border-sky-100 dark:border-sky-800/40',
                'accent' => 'sky',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
            ],
            self::OnlineBooking => [
                'bg' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-800/40',
                'accent' => 'indigo',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m-9 9a9 9 0 019-9"/>',
            ],
            self::OnlinePayment => [
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/40',
                'accent' => 'emerald',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
            ],
            self::SmsReminders => [
                'bg' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800/40',
                'accent' => 'amber',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>',
            ],
            self::WaitingList => [
                'bg' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800/40',
                'accent' => 'rose',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            self::ClientPortal => [
                'bg' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/40',
                'accent' => 'blue',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>',
            ],
            self::ClientImportExport => [
                'bg' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border-teal-100 dark:border-teal-800/40',
                'accent' => 'teal',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            self::ClientWallet => [
                'bg' => 'bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 border-violet-100 dark:border-violet-800/40',
                'accent' => 'violet',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
            ],
            self::MultiVendor => [
                'bg' => 'bg-fuchsia-50 dark:bg-fuchsia-950/60 text-fuchsia-600 dark:text-fuchsia-400 border-fuchsia-100 dark:border-fuchsia-800/40',
                'accent' => 'fuchsia',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3"/>',
            ],
            self::WmsEnabled => [
                'bg' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-800/40',
                'accent' => 'purple',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
            ],
            self::Coupons => [
                'bg' => 'bg-pink-50 dark:bg-pink-950/60 text-pink-600 dark:text-pink-400 border-pink-100 dark:border-pink-800/40',
                'accent' => 'pink',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
            ],
        };
    }

    public function module(): string
    {
        return match ($this) {
            self::Appointments,
            self::BookingProviders,
            self::OnlineBooking,
            self::OnlinePayment,
            self::SmsReminders,
            self::WaitingList => 'booking',

            self::Clients,
            self::ClientPortal,
            self::ClientImportExport,
            self::ClientWallet => 'clients',

            self::MultiVendor,
            self::Products,
            self::Warehouses,
            self::Stores,
            self::WmsEnabled,
            self::Coupons,
            self::Orders => 'market',

            self::Invoices => 'services',
        };
    }

    public function moduleTitle(): string
    {
        return match ($this->module()) {
            'booking' => 'نوبت‌دهی',
            'clients' => 'مشتریان و بیماران',
            'market' => 'فروشگاه و انبار',
            'services' => 'سرویس و خدمات',
            default => 'سیستم',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::Invoices => 'سقف تعداد فاکتورهای صادره',
            self::Clients => 'سقف تعداد مشتریان / پرونده‌ها',
            self::Appointments => 'سقف تعداد نوبت‌ها',
            self::BookingProviders => 'سقف پزشکان و ارائه‌دهندگان نوبت',
            self::Products => 'سقف تعداد محصولات و کالاها',
            self::Warehouses => 'سقف تعداد انبارها',
            self::Stores => 'سقف تعداد شعب و فروشگاه‌ها',
            self::Orders => 'سقف تعداد سفارش‌ها',
            self::MultiVendor => 'قابلیت چندفروشگاهی (Marketplace)',
            self::WmsEnabled => 'انبارداری پیشرفته و حواله بین‌انباری',
            self::Coupons => 'کوپن‌ها و کدهای تخفیف دوره‌ای',
            self::OnlineBooking => 'ثبت نوبت آنلاین توسط مراجعین',
            self::OnlinePayment => 'پرداخت آنلاین بیعانه نوبت',
            self::SmsReminders => 'یادآوری پیامکی نوبت‌ها',
            self::WaitingList => 'صف انتظار نوبت‌های لغوشده',
            self::ClientPortal => 'پرتال اختصاصی بیمار / مشتری',
            self::ClientImportExport => 'ایمپورت و اکسپورت اکسل پرونده‌ها',
            self::ClientWallet => 'کیف پول و شارژ اعتباری مراجعین',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Appointments => 'حداکثر تعداد کل نوبت‌های قابل ثبت در بازه اشتراک (خالی = نامحدود)',
            self::BookingProviders => 'حداکثر تعداد پزشکان یا اپراتورهای دارای تقویم نوبت‌دهی مجزا (خالی = نامحدود)',
            self::OnlineBooking => 'امکان رزرو اینترنتی نوبت توسط بیماران از طریق لینک اختصاصی یا وبسایت',
            self::OnlinePayment => 'امکان اتصال به درگاه پرداخت برای دریافت بیعانه هنگام رزرو نوبت',
            self::SmsReminders => 'ارسال پیامک یادآوری خودکار به بیمار و پزشک قبل از فرا رسیدن موعد نوبت',
            self::WaitingList => 'سیستم هوشمند رزرو خودکار جایگزین در صورت انصراف یا لغو نوبت‌ها',

            self::Clients => 'حداکثر تعداد پرونده‌های مشتریان یا بیماران قابل ثبت در سامانه (خالی = نامحدود)',
            self::ClientPortal => 'امکان ورود مستقل بیماران با شماره موبایل به پنل خود و مشاهده سوابق',
            self::ClientImportExport => 'امکان استخراج یا ورود انبوه اطلاعات بیماران از طریق اکسل',
            self::ClientWallet => 'سیستم کیف پول و پس‌انداز اعتباری برای تسویه‌حساب‌های خدمات بعدی',

            self::MultiVendor => 'فعال بودن حالت چندفروشگاهی. در صورت غیرفعال بودن، سیستم صرفاً تک‌فروشگاهی اختصاصی است',
            self::Products => 'حداکثر تعداد اقلام کالا و خدمات قابل تعریف در کاتالوگ فروشگاه (خالی = نامحدود)',
            self::Warehouses => 'حداکثر تعداد انبارهای فیزیکی یا مجازی قابل مدیریت در سیستم (خالی = نامحدود)',
            self::Stores => 'حداکثر تعداد فروشگاه‌های ثبت‌شده در حالت چندفروشگاهی (خالی = نامحدود)',
            self::WmsEnabled => 'امکان ره‌گیری دقیق کاردکس کالا، انتقال بین انبارها و حسابداری موجودی',
            self::Coupons => 'امکان ساخت کدهای تخفیف درصدی و مبلغی برای سبدهای خرید مشتریان',
            self::Orders => 'حداکثر تعداد سفارش‌های قابل ثبت در بازه اشتراک (خالی = نامحدود)',

            self::Invoices => 'حداکثر تعداد فاکتورهای قابل صدور در بازه اشتراک (خالی = نامحدود)',
        };
    }

    public function unit(): ?string
    {
        return match ($this) {
            self::Invoices => 'فاکتور',
            self::Clients => 'پرونده',
            self::Appointments => 'نوبت',
            self::BookingProviders => 'پزشک / پرسنل',
            self::Products => 'محصول',
            self::Warehouses => 'انبار',
            self::Stores => 'فروشگاه',
            self::Orders => 'سفارش',
            default => null,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Invoices => 'receipt',
            self::Clients => 'users',
            self::Appointments => 'calendar',
            self::BookingProviders => 'user-md',
            self::Products => 'cube',
            self::Warehouses => 'archive',
            self::Stores => 'home',
            self::Orders => 'shopping-bag',
            self::OnlineBooking => 'globe',
            self::OnlinePayment => 'credit-card',
            self::SmsReminders => 'chat',
            self::WaitingList => 'clock',
            self::ClientPortal => 'login',
            self::ClientImportExport => 'file-spreadsheet',
            self::ClientWallet => 'wallet',
            self::MultiVendor => 'store',
            self::WmsEnabled => 'layers',
            self::Coupons => 'tag',
        };
    }

    public function default(): mixed
    {
        return match ($this) {
            self::OnlineBooking,
            self::SmsReminders,
            self::ClientPortal,
            self::ClientImportExport,
            self::Coupons => true,

            self::OnlinePayment,
            self::WaitingList,
            self::ClientWallet,
            self::MultiVendor,
            self::WmsEnabled => false,

            default => null,
        };
    }

    /**
     * Convert enum instance to standard catalog definition array.
     */
    public function toDefinition(): array
    {
        return [
            'module' => $this->module(),
            'module_title' => $this->moduleTitle(),
            'key' => $this->value,
            'title' => $this->title(),
            'description' => $this->description(),
            'type' => $this->isFeature() ? 'feature' : 'quota',
            'unit' => $this->unit(),
            'icon' => $this->icon(),
            'default' => $this->default(),
        ];
    }

    /**
     * Named routes that trigger this quota restriction on creation/action.
     *
     * @return array<int, string>
     */
    public function guardedRouteNames(): array
    {
        return match ($this) {
            self::Invoices => [
                'services.invoices.create',
                'services.invoices.store',
                'services.invoices.convertToInvoice',
                'admin.accounting.invoices.create',
                'admin.accounting.invoices.store',
            ],
            self::Clients => [
                'user.clients.create',
                'user.clients.store',
                'user.clients.quick-store',
                'clients.create',
                'clients.store',
            ],
            self::Appointments => [
                'user.booking.appointments.create',
                'user.booking.appointments.store',
                'booking.appointments.create',
                'booking.appointments.store',
            ],
            self::BookingProviders => [
                'user.booking.providers.create',
                'user.booking.providers.store',
                'booking.providers.create',
                'booking.providers.store',
            ],
            self::Products => [
                'user.market.master-products.create',
                'user.market.master-products.store',
                'user.market.vendor.products.create',
                'user.market.vendor.products.store',
                'market.products.create',
                'market.products.store',
            ],
            self::Warehouses => [
                'user.market.warehouses.create',
                'user.market.warehouses.store',
                'market.warehouses.create',
                'market.warehouses.store',
            ],
            self::Stores => [
                'user.market.vendors.create',
                'user.market.vendors.store',
                'market.vendors.create',
                'market.vendors.store',
            ],
            self::Orders => [
                'user.market.orders.create',
                'user.market.orders.store',
                'market.orders.create',
                'market.orders.store',
                'services.orders.create',
                'services.orders.store',
            ],
            self::ClientImportExport => [
                'user.settings.clients.import',
                'user.settings.clients.export',
            ],
            default => [],
        };
    }

    /**
     * Fallback route to safely redirect the user when quota is full.
     */
    public function fallbackRouteName(): string
    {
        return match ($this) {
            self::Invoices => 'services.invoices.index',
            self::Clients => 'user.clients.index',
            self::Appointments => 'user.booking.dashboard',
            self::BookingProviders => 'user.booking.dashboard',
            self::Products => 'user.market.master-products.index',
            self::Warehouses => 'user.market.dashboard',
            self::Stores => 'user.market.vendors.index',
            self::Orders => 'user.market.orders.index',
            self::ClientImportExport => 'user.clients.index',
            default => 'user.dashboard',
        };
    }

    /**
     * Resolve QuotaKey instance directly from a route name.
     */
    public static function fromRoute(?string $routeName): ?self
    {
        if (!$routeName) {
            return null;
        }

        foreach (self::cases() as $case) {
            if (in_array($routeName, $case->guardedRouteNames(), true)) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Resolve QuotaKey instance from string or QuotaKey instance safely.
     */
    public static function resolve(string|self|null $key): ?self
    {
        if ($key instanceof self) {
            return $key;
        }

        if (empty($key) || !is_string($key)) {
            return null;
        }

        return self::tryFrom(trim($key));
    }

    /**
     * Return all QuotaKey cases assigned to a specific module slug.
     *
     * @return array<int, self>
     */
    public static function forModule(string $module): array
    {
        $module = strtolower(trim($module));
        return array_values(array_filter(self::cases(), fn(self $case) => $case->module() === $module));
    }
}

