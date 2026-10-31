<?php

namespace Modules\ResellerHub\App\Enums;

use Modules\ResellerHub\App\Data\Onboarding\JourneyData;
use Modules\ResellerHub\App\Data\Onboarding\StepData;

enum OnboardingJourney: string
{
    case CRM_SETUP = 'crm_setup';

    public function title(): string
    {
        return match ($this) {
            self::CRM_SETUP => 'راه‌اندازی و فعال‌سازی سامانه CRM',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CRM_SETUP => 'مراحل اختصاصی پیکربندی هویت، دامنه، وایت‌لیبل و ارتقای پلن سازمانی',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CRM_SETUP => 'rocket-launch',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::CRM_SETUP => 1,
        };
    }

    /**
     * @return array<StepData>
     */
    public function steps(): array
    {
        return match ($this) {
            self::CRM_SETUP => array_map(
                fn(OnboardingStep $step) => $step->toStepData($this->value),
                OnboardingStep::cases()
            ),
        };
    }

    public function toJourneyData(): JourneyData
    {
        return new JourneyData(
            slug: $this->value,
            title: $this->title(),
            description: $this->description(),
            icon: $this->icon(),
            requiredModule: null,
            sortOrder: $this->sortOrder(),
            steps: $this->steps()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
