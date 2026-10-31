<?php

namespace Modules\ResellerHub\App\Enums;

use App\Models\User;
use Modules\Accounting\Entities\Invoice;
use Modules\Clients\Entities\Client;
use Modules\ResellerHub\App\Enums\QuotaKey;

enum OnboardingStepPreset: string
{
    case TEAM_MEMBER = 'team_member';
    case NEW_CLIENT = 'new_client';
    case SMS_SETUP = 'sms_setup';
    case WALLET_GATEWAY = 'wallet_gateway';
    case BOOKING_SETUP = 'booking_setup';
    case FIRST_INVOICE = 'first_invoice';
    case COMMUNITY_CHANNEL = 'community_channel';
    case MANUAL_TASK = 'manual_task';

    public function title(): string
    {
        return match ($this) {
            self::TEAM_MEMBER => __('resellerhub::messages.onboarding.presets.team_member_title'),
            self::NEW_CLIENT => __('resellerhub::messages.onboarding.presets.new_client_title'),
            self::SMS_SETUP => __('resellerhub::messages.onboarding.presets.sms_setup_title'),
            self::WALLET_GATEWAY => __('resellerhub::messages.onboarding.presets.wallet_gateway_title'),
            self::BOOKING_SETUP => __('resellerhub::messages.onboarding.presets.booking_setup_title'),
            self::FIRST_INVOICE => __('resellerhub::messages.onboarding.presets.first_invoice_title'),
            self::COMMUNITY_CHANNEL => __('resellerhub::messages.onboarding.presets.community_channel_title'),
            self::MANUAL_TASK => __('resellerhub::messages.onboarding.presets.manual_task_title'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TEAM_MEMBER => __('resellerhub::messages.onboarding.presets.team_member_desc'),
            self::NEW_CLIENT => __('resellerhub::messages.onboarding.presets.new_client_desc'),
            self::SMS_SETUP => __('resellerhub::messages.onboarding.presets.sms_setup_desc'),
            self::WALLET_GATEWAY => __('resellerhub::messages.onboarding.presets.wallet_gateway_desc'),
            self::BOOKING_SETUP => __('resellerhub::messages.onboarding.presets.booking_setup_desc'),
            self::FIRST_INVOICE => __('resellerhub::messages.onboarding.presets.first_invoice_desc'),
            self::COMMUNITY_CHANNEL => __('resellerhub::messages.onboarding.presets.community_channel_desc'),
            self::MANUAL_TASK => __('resellerhub::messages.onboarding.presets.manual_task_desc'),
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::TEAM_MEMBER => __('resellerhub::messages.onboarding.presets.team_member_badge'),
            self::NEW_CLIENT => __('resellerhub::messages.onboarding.presets.new_client_badge'),
            self::SMS_SETUP => __('resellerhub::messages.onboarding.presets.sms_setup_badge'),
            self::WALLET_GATEWAY => __('resellerhub::messages.onboarding.presets.wallet_gateway_badge'),
            self::BOOKING_SETUP => __('resellerhub::messages.onboarding.presets.booking_setup_badge'),
            self::FIRST_INVOICE => __('resellerhub::messages.onboarding.presets.first_invoice_badge'),
            self::COMMUNITY_CHANNEL => __('resellerhub::messages.onboarding.presets.community_channel_badge'),
            self::MANUAL_TASK => __('resellerhub::messages.onboarding.presets.manual_task_badge'),
        };
    }

    public function iconEnum(): OnboardingStepIcon
    {
        return match ($this) {
            self::TEAM_MEMBER => OnboardingStepIcon::USERS,
            self::NEW_CLIENT => OnboardingStepIcon::USER_PLUS,
            self::SMS_SETUP => OnboardingStepIcon::CHAT_BUBBLE,
            self::WALLET_GATEWAY => OnboardingStepIcon::CREDIT_CARD,
            self::BOOKING_SETUP => OnboardingStepIcon::CALENDAR_DAYS,
            self::FIRST_INVOICE => OnboardingStepIcon::DOCUMENT_TEXT,
            self::COMMUNITY_CHANNEL => OnboardingStepIcon::MEGAPHONE,
            self::MANUAL_TASK => OnboardingStepIcon::CLIPBOARD_DOCUMENT_CHECK,
        };
    }

    public function icon(): string
    {
        return $this->iconEnum()->value;
    }

    public function actionType(): string
    {
        return match ($this) {
            self::COMMUNITY_CHANNEL => 'external',
            self::MANUAL_TASK => 'manual',
            default => 'internal',
        };
    }

    public function internalRouteEnum(): ?OnboardingInternalRoute
    {
        return match ($this) {
            self::TEAM_MEMBER => OnboardingInternalRoute::USERS_CREATE,
            self::NEW_CLIENT => OnboardingInternalRoute::CLIENTS_CREATE,
            self::SMS_SETUP => OnboardingInternalRoute::SMS_SETTINGS,
            self::WALLET_GATEWAY => OnboardingInternalRoute::WALLET_GATEWAY,
            self::BOOKING_SETUP => OnboardingInternalRoute::BOOKING_CALENDAR,
            self::FIRST_INVOICE => OnboardingInternalRoute::INVOICES_CREATE,
            default => null,
        };
    }

    public function internalRoute(): ?string
    {
        return $this->internalRouteEnum()?->value;
    }

    public function actionUrl(): ?string
    {
        return match ($this) {
            self::TEAM_MEMBER => '/admin/users/create',
            self::NEW_CLIENT => '/user/clients/create',
            self::SMS_SETUP => '/user/sms',
            self::WALLET_GATEWAY => '/user/wallet',
            self::BOOKING_SETUP => '/user/booking',
            self::FIRST_INVOICE => '/user/invoices/create',
            self::COMMUNITY_CHANNEL => 'https://t.me',
            self::MANUAL_TASK => null,
        };
    }

    public function canManualComplete(): bool
    {
        return match ($this) {
            self::COMMUNITY_CHANNEL, self::MANUAL_TASK => true,
            default => false,
        };
    }

    public function triggerModel(): ?string
    {
        return match ($this) {
            self::TEAM_MEMBER => User::class,
            self::NEW_CLIENT => Client::class,
            self::FIRST_INVOICE => class_exists(\Modules\Services\App\Http\Models\Invoice::class)
                ? \Modules\Services\App\Http\Models\Invoice::class
                : Invoice::class,
            default => null,
        };
    }

    public function requiredQuota(): ?QuotaKey
    {
        return match ($this) {
            self::NEW_CLIENT => QuotaKey::Clients,
            self::FIRST_INVOICE => QuotaKey::Invoices,
            self::BOOKING_SETUP => QuotaKey::Appointments,
            default => null,
        };
    }

    /**
     * Structure presets for frontend Alpine.js.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function toFrontendArray(): array
    {
        return array_map(fn (self $case) => [
            'key' => $case->value,
            'title' => $case->title(),
            'description' => $case->description(),
            'badge' => $case->badge(),
            'icon' => $case->icon(),
            'action_type' => $case->actionType(),
            'internal_route' => $case->internalRoute() ?? '',
            'action_url' => $case->actionUrl() ?? '',
            'can_manual_complete' => $case->canManualComplete(),
            'required_quota' => $case->requiredQuota()?->value ?? '',
            'is_required' => false,
        ], self::cases());
    }
}
