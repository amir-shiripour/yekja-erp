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
    case OFFICE_DETAILS = 'office_details';
    case BUSINESS_TARGET = 'business_target';

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
            self::OFFICE_DETAILS => __('resellerhub::messages.onboarding.presets.office_details_title'),
            self::BUSINESS_TARGET => __('resellerhub::messages.onboarding.presets.business_target_title'),
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
            self::OFFICE_DETAILS => __('resellerhub::messages.onboarding.presets.office_details_desc'),
            self::BUSINESS_TARGET => __('resellerhub::messages.onboarding.presets.business_target_desc'),
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
            self::OFFICE_DETAILS => __('resellerhub::messages.onboarding.presets.office_details_badge'),
            self::BUSINESS_TARGET => __('resellerhub::messages.onboarding.presets.business_target_badge'),
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
            self::OFFICE_DETAILS => OnboardingStepIcon::CLIPBOARD_DOCUMENT_LIST,
            self::BUSINESS_TARGET => OnboardingStepIcon::CHART_BAR,
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
            self::OFFICE_DETAILS, self::BUSINESS_TARGET => 'custom_fields',
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
            self::MANUAL_TASK, self::OFFICE_DETAILS, self::BUSINESS_TARGET => null,
        };
    }

    public function canManualComplete(): bool
    {
        return match ($this) {
            self::COMMUNITY_CHANNEL, self::MANUAL_TASK => true,
            default => false,
        };
    }

    public function customFields(): ?array
    {
        return match ($this) {
            self::OFFICE_DETAILS => [
                [
                    'name' => 'fax',
                    'label' => __('resellerhub::messages.onboarding.presets.office_details_fax_label'),
                    'type' => 'tel',
                    'is_required' => false,
                    'placeholder' => '021-xxxxxxxx',
                ],
                [
                    'name' => 'postal_code',
                    'label' => __('resellerhub::messages.onboarding.presets.office_details_postal_label'),
                    'type' => 'number',
                    'is_required' => false,
                    'placeholder' => '1234567890',
                ],
            ],
            self::BUSINESS_TARGET => [
                [
                    'name' => 'monthly_target',
                    'label' => __('resellerhub::messages.onboarding.presets.business_target_monthly_label'),
                    'type' => 'number',
                    'is_required' => false,
                    'placeholder' => 'مثال: ۵۰۰۰۰۰۰۰',
                ],
                [
                    'name' => 'staff_count',
                    'label' => __('resellerhub::messages.onboarding.presets.business_target_staff_label'),
                    'type' => 'number',
                    'is_required' => false,
                    'placeholder' => 'مثال: ۵',
                ],
            ],
            default => null,
        };
    }

    public function fieldGroup(): ?string
    {
        return match ($this) {
            self::OFFICE_DETAILS => 'contact',
            self::BUSINESS_TARGET => 'business',
            default => null,
        };
    }

    public function targetScope(): string
    {
        return match ($this) {
            self::BUSINESS_TARGET => 'tenant',
            default => 'user',
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
            'custom_fields' => $case->customFields(),
            'field_group' => $case->fieldGroup(),
            'target_scope' => $case->targetScope(),
        ], self::cases());
    }
}
