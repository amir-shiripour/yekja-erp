<?php

namespace Modules\ResellerHub\App\Enums;

use Modules\ResellerHub\App\Data\Onboarding\StepData;

enum OnboardingStep: string
{
    case COMPLETE_KYC = 'complete_kyc';
    case CONFIGURE_DOMAIN = 'configure_domain';
    case CONFIGURE_BRANDING = 'configure_branding';
    case SELECT_PACKAGE = 'select_package';

    public function title(): string
    {
        return match ($this) {
            self::COMPLETE_KYC => __('resellerhub::messages.onboarding.default_steps.kyc_title'),
            self::CONFIGURE_DOMAIN => __('resellerhub::messages.onboarding.default_steps.domain_title'),
            self::CONFIGURE_BRANDING => __('resellerhub::messages.onboarding.default_steps.branding_title'),
            self::SELECT_PACKAGE => __('resellerhub::messages.onboarding.default_steps.package_title'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::COMPLETE_KYC => __('resellerhub::messages.onboarding.default_steps.kyc_desc'),
            self::CONFIGURE_DOMAIN => __('resellerhub::messages.onboarding.default_steps.domain_desc'),
            self::CONFIGURE_BRANDING => __('resellerhub::messages.onboarding.default_steps.branding_desc'),
            self::SELECT_PACKAGE => __('resellerhub::messages.onboarding.default_steps.package_desc'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMPLETE_KYC => 'identification',
            self::CONFIGURE_DOMAIN => 'globe-alt',
            self::CONFIGURE_BRANDING => 'paint-brush',
            self::SELECT_PACKAGE => 'sparkles',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::COMPLETE_KYC => __('resellerhub::messages.onboarding.default_steps.kyc_badge'),
            self::CONFIGURE_DOMAIN => __('resellerhub::messages.onboarding.default_steps.domain_badge'),
            self::CONFIGURE_BRANDING => __('resellerhub::messages.onboarding.default_steps.branding_badge'),
            self::SELECT_PACKAGE => __('resellerhub::messages.onboarding.default_steps.package_badge'),
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::COMPLETE_KYC => 1,
            self::CONFIGURE_DOMAIN => 2,
            self::CONFIGURE_BRANDING => 3,
            self::SELECT_PACKAGE => 4,
        };
    }

    public function dependsOn(): ?self
    {
        return match ($this) {
            self::COMPLETE_KYC => null,
            self::CONFIGURE_DOMAIN => self::COMPLETE_KYC,
            self::CONFIGURE_BRANDING => self::CONFIGURE_DOMAIN,
            self::SELECT_PACKAGE => self::CONFIGURE_BRANDING,
        };
    }

    public function routeAction(): string
    {
        return 'user.dashboard';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function actionType(): string
    {
        return 'modal';
    }

    public function actionTarget(): string
    {
        return match ($this) {
            self::COMPLETE_KYC => 'kyc',
            self::CONFIGURE_DOMAIN => 'domain',
            self::CONFIGURE_BRANDING => 'branding',
            self::SELECT_PACKAGE => 'package',
        };
    }

    public function actionUrl(): ?string
    {
        return null;
    }

    public function isCore(): bool
    {
        return true;
    }

    public function canManualComplete(): bool
    {
        return false;
    }

    /**
     * Convert the enum case to its array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->value,
            'is_core' => $this->isCore(),
            'enabled' => true,
            'is_required' => $this->isRequired(),
            'sort_order' => $this->sortOrder(),
            'title' => $this->title(),
            'description' => $this->description(),
            'badge' => $this->badge(),
            'icon' => $this->icon(),
            'depends_on' => $this->dependsOn()?->value,
            'action_type' => $this->actionType(),
            'action_target' => $this->actionTarget(),
            'action_url' => $this->actionUrl(),
            'can_manual_complete' => $this->canManualComplete(),
        ];
    }

    /**
     * Get all default core onboarding steps as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultStepsArray(): array
    {
        return array_map(static fn (self $step): array => $step->toArray(), self::cases());
    }

    public function toStepData(string $journey = 'crm_setup'): StepData
    {
        return new StepData(
            slug: $this->value,
            journey: $journey,
            title: $this->title(),
            description: $this->description(),
            routeAction: $this->routeAction(),
            icon: $this->icon(),
            requiredModule: null,
            dependsOn: $this->dependsOn()?->value,
            completionTriggerModel: null,
            sortOrder: $this->sortOrder(),
            badge: $this->badge(),
            isRequired: $this->isRequired(),
            actionType: $this->actionType(),
            actionTarget: $this->actionTarget(),
            actionUrl: $this->actionUrl(),
            isCore: $this->isCore(),
            canManualComplete: $this->canManualComplete(),
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromOrDefault(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom(trim($value));
    }
}

