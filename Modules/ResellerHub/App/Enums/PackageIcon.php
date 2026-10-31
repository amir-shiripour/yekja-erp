<?php

namespace Modules\ResellerHub\App\Enums;

enum PackageIcon: string
{
    case TOOTH = 'tooth';
    case STETHOSCOPE = 'stethoscope';
    case BUILDING = 'building';
    case UTENSILS = 'utensils';
    case SCALES = 'scales';
    case SCISSORS = 'scissors';
    case SHOPPING_CART = 'shopping-cart';
    case CART = 'cart';
    case CAR = 'car';
    case DUMBBELL = 'dumbbell';
    case BOOK = 'book';
    case FACTORY = 'factory';
    case CALCULATOR = 'calculator';
    case HARD_HAT = 'hard-hat';
    case PLANE = 'plane';
    case CAMERA = 'camera';
    case LAPTOP = 'laptop';
    case TRUCK = 'truck';
    case PILL = 'pill';
    case DIAMOND = 'diamond';
    case UMBRELLA = 'umbrella';
    case BRIEFCASE = 'briefcase';
    case CROWN = 'crown';
    case STAR = 'star';
    case HEART = 'heart';
    case HOME = 'home';
    case SHOP = 'shop';
    case SHIELD = 'shield';
    case SPARKLES = 'sparkles';
    case ACADEMIC = 'academic';
    case CODE = 'code';
    case CUBE = 'cube';

    public function label(): string
    {
        return match ($this) {
            self::TOOTH => 'دندانپزشکی و کلینیک دندان',
            self::STETHOSCOPE => 'پزشکی، مطب و درمانگاه',
            self::BUILDING => 'املاک، مسکن و مستغلات',
            self::UTENSILS => 'رستوران، کافه و غذا',
            self::SCALES => 'وکالت، حقوقی و داوری',
            self::SCISSORS => 'سالن زیبایی و آرایشگاه',
            self::SHOPPING_CART, self::CART => 'فروشگاه و هایپرمارکت',
            self::CAR => 'خودرو، نمایشگاه و تعمیرگاه',
            self::DUMBBELL => 'باشگاه ورزشی و فیتنس',
            self::BOOK => 'آموزشگاه، مدارس و آکادمی',
            self::FACTORY => 'کارخانجات و صنایع تولیدی',
            self::CALCULATOR => 'حسابداری، مالی و صرافی',
            self::HARD_HAT => 'مهندسی، معماری و عمران',
            self::PLANE => 'آژانس هواپیمایی و گردشگری',
            self::CAMERA => 'آتلیه، عکاسی و فیلمبرداری',
            self::LAPTOP => 'فناوری اطلاعات و نرم‌افزار',
            self::TRUCK => 'باربری، پخش و لجستیک',
            self::PILL => 'داروخانه و تجهیزات پزشکی',
            self::DIAMOND => 'طلا، جواهرات و لوکس',
            self::UMBRELLA => 'بیمه و خدمات حمایتی',
            self::BRIEFCASE => 'شرکتی، اداری و بازرگانی',
            self::CROWN => 'ویژه، VIP و ممتاز',
            self::STAR => 'برتر و منتخب',
            self::HEART => 'سلامت، خیریه و مراقبتی',
            self::HOME => 'خانگی، اقامتی و هتل',
            self::SHOP => 'بوتیک و فروشگاه حضوری',
            self::SHIELD => 'امنیت، حراست و نظارت',
            self::SPARKLES => 'خدمات ویژه و خلاقانه',
            self::ACADEMIC => 'دانشگاهی و پژوهشی',
            self::CODE => 'توسعه، وب و برنامه‌نویسی',
            self::CUBE => 'پکیج عمومی و سازمانی',
        };
    }

    public function svgPath(): string
    {
        return match ($this) {
            self::TOOTH => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5c-.88-1.77-1.5-3.83-1.5-5.5a4 4 0 0 1 7.42-2.05L12 5l1.58-2.05A4 4 0 0 1 21 5c0 1.67-.62 3.73-1.5 5.5-1.34 2.68-2.5 6.5-4 10.5-1.25 0-1.88-1.5-2.5-3.5-.4-.25-.8-.25-1.2 0-.62 2-1.25 3.5-2.5 3.5-1.5-4-2.66-7.82-4-10.5z"/>',
            self::STETHOSCOPE => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3v5a4.5 4.5 0 009 0V3M6 3h-3M15 3h-3M9 12.5v3.5a4 4 0 004 4h1a4 4 0 004-4v-1.5M18 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>',
            self::BUILDING => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m6-14h.01M9 11h.01M9 15h.01M15 7h.01M15 11h.01M15 15h.01M12 21v-4h0"/>',
            self::UTENSILS => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 4v7a2 2 0 01-2 2h-1a2 2 0 01-2-2V4M15 13v7M6 4v3a3 3 0 006 0V4M9 10v10"/>',
            self::SCALES => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 7h-6l3-7zm15 0l3 7h-6l3-7zm-9-3h12M12 3v18m-4 0h8"/>',
            self::SCISSORS => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
            self::SHOPPING_CART, self::CART => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
            self::CAR => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 17h14M5 17a2 2 0 01-2-2V9a2 2 0 012-2h1.5L8 4h8l1.5 3H19a2 2 0 012 2v6a2 2 0 01-2 2M5 17a2 2 0 104 0m6 0a2 2 0 104 0M7 11h.01M17 11h.01"/>',
            self::DUMBBELL => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 5v14M3 7v10M9 8v8m9-11v14m3-12v10m-6-9v8M9 12h6"/>',
            self::BOOK => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
            self::FACTORY => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V9l-5 4V9l-5 4V3H3v18h16zM9 17h.01M14 17h.01"/>',
            self::CALCULATOR => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>',
            self::HARD_HAT => '<path stroke-linecap="round" stroke-linejoin="round" d="M2 18h20M4 18a8 8 0 0116 0M10 10V6a2 2 0 014 0v4"/>',
            self::PLANE => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-7-8V3a2 2 0 00-4 0v10l-7 8 9-2zm0 0v-5"/>',
            self::CAMERA => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
            self::LAPTOP => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            self::TRUCK => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h10zm0 0h3.586a1 1 0 00.707-.293l2.414-2.414a1 1 0 00.293-.707V10a1 1 0 00-1-1h-5v7z"/>',
            self::PILL => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.5 4.5l5 5a4.24 4.24 0 010 6l-5 5a4.24 4.24 0 01-6 0l-5-5a4.24 4.24 0 010-6l5-5a4.24 4.24 0 016 0zM8.5 10.5l5 5"/>',
            self::DIAMOND => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12l4 6-10 12L2 9l4-6zm-4 6h20M12 21L8 9M12 21l4-12M6 3l2 6M18 3l-2 6"/>',
            self::UMBRELLA => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 0a9 9 0 019 9H3a9 9 0 019-9zm0 9v6a2 2 0 01-2 2h-1"/>',
            self::BRIEFCASE => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            self::CROWN => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 18h18M4 14l3-7 5 5 5-5 3 7H4z"/>',
            self::STAR => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
            self::HEART => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            self::HOME => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            self::SHOP => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
            self::SHIELD => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            self::SPARKLES => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
            self::ACADEMIC => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/>',
            self::CODE => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
            self::CUBE => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        };
    }

    public function svg(string $class = 'w-5 h-5'): string
    {
        return sprintf(
            '<svg class="%s" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">%s</svg>',
            e($class),
            $this->svgPath()
        );
    }

    public static function list(): array
    {
        $res = [];
        foreach (self::cases() as $case) {
            if ($case === self::CART) continue; // Alias for shopping-cart
            $res[] = [
                'key' => $case->value,
                'label' => $case->label(),
            ];
        }
        return $res;
    }

    public static function svgMap(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->value] = $case->svgPath();
        }
        return $map;
    }

    public static function fromOrDefault(?string $value): self
    {
        if (empty($value)) {
            return self::CUBE;
        }

        $clean = strtolower(trim($value));
        return self::tryFrom($clean) ?? self::CUBE;
    }
}
