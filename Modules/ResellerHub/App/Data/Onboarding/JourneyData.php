<?php

namespace Modules\ResellerHub\App\Data\Onboarding;

use Spatie\LaravelData\Data;

class JourneyData extends Data
{
    /**
     * @param array<StepData> $steps
     */
    public function __construct(
        public string  $slug,
        public string  $title,
        public string  $description,
        public string  $icon = 'academic-cap',
        public ?string $requiredModule = null,
        public int     $sortOrder = 0,
        public array   $steps = [],
    )
    {
    }
}
