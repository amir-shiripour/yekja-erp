<?php

namespace Modules\ResellerHub\App\Data\Onboarding;

use Spatie\LaravelData\Data;

class StepData extends Data
{
    public function __construct(
        public string  $slug,
        public string  $journey,
        public string  $title,
        public string  $description,
        public string  $routeAction,
        public string  $icon = 'check-circle',
        public ?string $requiredModule = null,
        public ?string $requiredQuota = null,
        public ?string $dependsOn = null,
        public ?string $completionTriggerModel = null,
        public int     $sortOrder = 0,
        public ?string $badge = null,
        public bool    $isRequired = true,
        public string  $actionType = 'modal',
        public ?string $actionTarget = null,
        public ?string $actionUrl = null,
        public bool    $isCore = true,
        public bool    $canManualComplete = false,
    )
    {
    }
}
