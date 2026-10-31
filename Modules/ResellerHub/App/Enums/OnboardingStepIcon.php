<?php

namespace Modules\ResellerHub\App\Enums;

enum OnboardingStepIcon: string
{
    case USERS = 'users';
    case USER_PLUS = 'user-plus';
    case CHAT_BUBBLE = 'chat-bubble-left-right';
    case CREDIT_CARD = 'credit-card';
    case CALENDAR_DAYS = 'calendar-days';
    case DOCUMENT_TEXT = 'document-text';
    case VIDEO_CAMERA = 'video-camera';
    case MEGAPHONE = 'megaphone';
    case SPARKLES = 'sparkles';
    case SHIELD_CHECK = 'shield-check';
    case GLOBE_ALT = 'globe-alt';
    case PAINT_BRUSH = 'paint-brush';
    case CLIPBOARD_DOCUMENT_CHECK = 'clipboard-document-check';
    case ROCKET_LAUNCH = 'rocket-launch';
    case BUILDING_OFFICE = 'building-office';
    case PHONE = 'phone';
    case CHECK_CIRCLE = 'check-circle';

    public function label(): string
    {
        return match ($this) {
            self::USERS => __('resellerhub::messages.onboarding.icons.users'),
            self::USER_PLUS => __('resellerhub::messages.onboarding.icons.user_plus'),
            self::CHAT_BUBBLE => __('resellerhub::messages.onboarding.icons.chat_bubble'),
            self::CREDIT_CARD => __('resellerhub::messages.onboarding.icons.credit_card'),
            self::CALENDAR_DAYS => __('resellerhub::messages.onboarding.icons.calendar_days'),
            self::DOCUMENT_TEXT => __('resellerhub::messages.onboarding.icons.document_text'),
            self::VIDEO_CAMERA => __('resellerhub::messages.onboarding.icons.video_camera'),
            self::MEGAPHONE => __('resellerhub::messages.onboarding.icons.megaphone'),
            self::SPARKLES => __('resellerhub::messages.onboarding.icons.sparkles'),
            self::SHIELD_CHECK => __('resellerhub::messages.onboarding.icons.shield_check'),
            self::GLOBE_ALT => __('resellerhub::messages.onboarding.icons.globe_alt'),
            self::PAINT_BRUSH => __('resellerhub::messages.onboarding.icons.paint_brush'),
            self::CLIPBOARD_DOCUMENT_CHECK => __('resellerhub::messages.onboarding.icons.clipboard_document_check'),
            self::ROCKET_LAUNCH => __('resellerhub::messages.onboarding.icons.rocket_launch'),
            self::BUILDING_OFFICE => __('resellerhub::messages.onboarding.icons.building_office'),
            self::PHONE => __('resellerhub::messages.onboarding.icons.phone'),
            self::CHECK_CIRCLE => __('resellerhub::messages.onboarding.icons.check_circle'),
        };
    }

    public function svg(): string
    {
        return match ($this) {
            self::USERS => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
            self::USER_PLUS => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>',
            self::CHAT_BUBBLE => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
            self::CREDIT_CARD => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
            self::CALENDAR_DAYS => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
            self::DOCUMENT_TEXT => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
            self::VIDEO_CAMERA => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>',
            self::MEGAPHONE => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>',
            self::SPARKLES => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>',
            self::SHIELD_CHECK => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
            self::GLOBE_ALT => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>',
            self::PAINT_BRUSH => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4 4.001 4.001 0 013-3.874V7a4 4 0 018 0v6.126A4.001 4.001 0 0117 17a4 4 0 01-4 4H7zm0 0h6" /></svg>',
            self::CLIPBOARD_DOCUMENT_CHECK => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>',
            self::ROCKET_LAUNCH => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 3.39a1.5 1.5 0 11-2.12-2.122 1.5 1.5 0 012.12 2.122z" /></svg>',
            self::BUILDING_OFFICE => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
            self::PHONE => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>',
            self::CHECK_CIRCLE => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        };
    }

    public static function defaultSvg(): string
    {
        return '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>';
    }

    public static function renderSvg(?string $iconId): string
    {
        if (! $iconId) {
            return self::defaultSvg();
        }

        $case = self::tryFrom($iconId);

        return $case ? $case->svg() : self::defaultSvg();
    }

    /**
     * @return array<int, array{id: string, label: string, svg: string}>
     */
    public static function toFrontendArray(): array
    {
        return array_map(fn (self $case) => [
            'id' => $case->value,
            'label' => $case->label(),
            'svg' => $case->svg(),
        ], self::cases());
    }
}
