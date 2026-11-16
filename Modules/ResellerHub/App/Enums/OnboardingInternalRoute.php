<?php

namespace Modules\ResellerHub\App\Enums;

enum OnboardingInternalRoute: string
{
    case CLIENTS_CREATE = 'user/clients/create';
    case USERS_CREATE = 'user/users/create';
    case USERS_LIST = 'user/users';
    case BOOKING_PROVIDERS = 'user/booking/providers';
    case BOOKING_CALENDAR = 'user/booking';
    case SMS_SETTINGS = 'user/sms/settings';
    case WALLET_GATEWAY = 'user/wallet';
    case INVOICES_CREATE = 'user/services/invoices/create';
    case WORKFLOWS = 'user/workflows';
    case SETTINGS = 'user/settings';
    case CUSTOM = 'custom';

    public function title(): string
    {
        return match ($this) {
            self::CLIENTS_CREATE => __('resellerhub::messages.onboarding.routes.clients_create'),
            self::USERS_CREATE => __('resellerhub::messages.onboarding.routes.users_create'),
            self::USERS_LIST => __('resellerhub::messages.onboarding.routes.users_list'),
            self::BOOKING_PROVIDERS => __('resellerhub::messages.onboarding.routes.booking_providers'),
            self::SMS_SETTINGS => __('resellerhub::messages.onboarding.routes.sms_settings'),
            self::WALLET_GATEWAY => __('resellerhub::messages.onboarding.routes.wallet_gateway'),
            self::BOOKING_CALENDAR => __('resellerhub::messages.onboarding.routes.booking_calendar'),
            self::INVOICES_CREATE => __('resellerhub::messages.onboarding.routes.invoices_create'),
            self::WORKFLOWS => __('resellerhub::messages.onboarding.routes.workflows'),
            self::SETTINGS => __('resellerhub::messages.onboarding.routes.settings'),
            self::CUSTOM => __('resellerhub::messages.onboarding.routes.custom'),
        };
    }

    /**
     * @return array<int, array{route: string, title: string}>
     */
    public static function toFrontendArray(): array
    {
        return array_map(fn(self $case) => [
            'route' => $case->value,
            'title' => $case->title(),
        ], self::cases());
    }
}
