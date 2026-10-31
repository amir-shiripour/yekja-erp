<?php

namespace Modules\ResellerHub\App\Services\Onboarding;

use App\Models\Tenant;
use App\Models\User;
use Modules\Clients\Entities\Client;
use Modules\ResellerHub\App\Data\Onboarding\JourneyData;
use Modules\ResellerHub\App\Data\Onboarding\StepData;
use Modules\ResellerHub\App\Enums\OnboardingJourney;
use Modules\ResellerHub\App\Enums\OnboardingStepPreset;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\Services\App\Http\Models\Invoice;

class OnboardingRegistry
{
    /**
     * @var array<string, JourneyData>
     */
    protected array $journeys = [];

    /**
     * @var array<string, StepData>
     */
    protected array $steps = [];

    /**
     * Maps model class names to an array of step slugs.
     *
     * @var array<string, array<string>>
     */
    protected array $modelTriggers = [];

    /**
     * Register a new onboarding journey.
     */
    public function registerJourney(JourneyData $journey): self
    {
        $this->journeys[$journey->slug] = $journey;

        foreach ($journey->steps as $step) {
            $this->registerStep($step);
        }

        return $this;
    }

    /**
     * Register an individual onboarding step.
     */
    public function registerStep(StepData $step): self
    {
        $this->steps[$step->slug] = $step;

        // If this step is linked to an Eloquent model completion trigger
        if ($step->completionTriggerModel) {
            $modelClass = ltrim($step->completionTriggerModel, '\\');
            if (!isset($this->modelTriggers[$modelClass])) {
                $this->modelTriggers[$modelClass] = [];
            }
            if (!in_array($step->slug, $this->modelTriggers[$modelClass], true)) {
                $this->modelTriggers[$modelClass][] = $step->slug;
            }
        }

        // Keep journey's steps list updated if journey is already registered
        if (isset($this->journeys[$step->journey])) {
            $journey = $this->journeys[$step->journey];
            $exists = false;
            foreach ($journey->steps as $idx => $s) {
                if ($s->slug === $step->slug) {
                    $journey->steps[$idx] = $step;
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $journey->steps[] = $step;
            }
            usort($journey->steps, fn(StepData $a, StepData $b) => $a->sortOrder <=> $b->sortOrder);
            $this->journeys[$step->journey] = $journey;
        }

        return $this;
    }

    /**
     * Get a registered journey by slug.
     */
    public function getJourney(string $slug): ?JourneyData
    {
        return $this->journeys[$slug] ?? null;
    }

    /**
     * Get a registered step by slug.
     */
    public function getStep(string $slug): ?StepData
    {
        if (isset($this->steps[$slug])) {
            return $this->steps[$slug];
        }

        // Check in all default steps
        foreach (ResellerSetting::getDefaultOnboardingSteps() as $cs) {
            if ($cs['slug'] === $slug) {
                return new StepData(
                    slug: $cs['slug'],
                    journey: OnboardingJourney::CRM_SETUP->value,
                    title: $cs['title'] ?? $cs['slug'],
                    description: $cs['description'] ?? '',
                    routeAction: 'user.dashboard',
                    icon: $cs['icon'] ?? 'check-circle',
                    requiredModule: null,
                    requiredQuota: $cs['required_quota'] ?? null,
                    dependsOn: $cs['depends_on'] ?? null,
                    completionTriggerModel: null,
                    sortOrder: (int)($cs['sort_order'] ?? 0),
                    badge: $cs['badge'] ?? null,
                    isRequired: true,
                    actionType: $cs['action_type'] ?? 'modal',
                    actionTarget: $cs['action_target'] ?? null,
                    actionUrl: $cs['action_url'] ?? null,
                    isCore: true,
                    canManualComplete: !empty($cs['can_manual_complete']),
                );
            }
        }

        return null;
    }

    /**
     * Get all registered journeys ordered by sortOrder.
     *
     * @return array<string, JourneyData>
     */
    public function getAllJourneys(): array
    {
        $journeys = $this->journeys;
        uasort($journeys, fn(JourneyData $a, JourneyData $b) => $a->sortOrder <=> $b->sortOrder);
        return $journeys;
    }

    /**
     * Get all registered steps ordered by sortOrder.
     *
     * @return array<string, StepData>
     */
    public function getAllSteps(): array
    {
        $steps = $this->steps;
        uasort($steps, fn(StepData $a, StepData $b) => $a->sortOrder <=> $b->sortOrder);
        return $steps;
    }

    /**
     * Filter journeys accessible to the given tenant based on active module entitlements.
     *
     * @return array<string, JourneyData>
     */
    public function getJourneysForTenant(?Tenant $tenant = null): array
    {
        if (!$tenant) {
            return $this->getAllJourneys();
        }

        $resellerId = $tenant->reseller_id;
        $tourConfig = ResellerSetting::getOnboardingTourConfig($resellerId);

        // If reseller explicitly disabled onboarding tour
        if (empty($tourConfig['enabled'])) {
            return [];
        }

        $configuredSteps = $tourConfig['steps'] ?? [];
        $activeModules = array_map('strtolower', $tenant->activeModuleSlugs());

        $dynamicSteps = [];
        foreach ($configuredSteps as $cs) {
            if (empty($cs['enabled'])) {
                continue;
            }

            $completionTriggerModel = !empty($cs['completion_trigger_model']) ? (string)$cs['completion_trigger_model'] : null;
            $actUrl = strtolower((string)($cs['action_url'] ?? ''));
            $actTarget = strtolower((string)($cs['action_target'] ?? ''));
            $slug = strtolower((string)$cs['slug']);
            $actionType = (string)($cs['action_type'] ?? 'modal');

            // Detect if step matches a standardized OnboardingStepPreset
            $matchedPreset = null;
            foreach (OnboardingStepPreset::cases() as $presetCase) {
                if ($slug === $presetCase->value || str_contains($slug, $presetCase->value) || ($presetCase->actionUrl() && $actUrl === strtolower($presetCase->actionUrl()))) {
                    $matchedPreset = $presetCase;
                    break;
                }
            }

            if (!$completionTriggerModel) {
                if ($matchedPreset?->triggerModel()) {
                    $completionTriggerModel = $matchedPreset->triggerModel();
                } elseif (str_contains($actUrl, 'client') || str_contains($actTarget, 'client') || str_contains($slug, 'client')) {
                    $completionTriggerModel = Client::class;
                } elseif (str_contains($actUrl, 'invoice') || str_contains($actTarget, 'invoice') || str_contains($slug, 'invoice')) {
                    $completionTriggerModel = class_exists(Invoice::class)
                        ? Invoice::class
                        : \Modules\Accounting\Entities\Invoice::class;
                } elseif (str_contains($actUrl, 'user') || str_contains($actTarget, 'team') || str_contains($slug, 'team')) {
                    $completionTriggerModel = User::class;
                }
            }

            // External links and manual checklist steps must always allow manual completion
            $canManualComplete = !empty($cs['can_manual_complete']);
            if (in_array($actionType, ['external', 'link', 'manual'], true)) {
                $canManualComplete = true;
            }

            // Resolve required quota key (explicit, preset-based, or inferred)
            $requiredQuota = !empty($cs['required_quota']) ? (string)$cs['required_quota'] : null;
            if (!$requiredQuota) {
                if ($matchedPreset?->requiredQuota()) {
                    $requiredQuota = $matchedPreset->requiredQuota()->value;
                } elseif (str_contains($actUrl, 'client') || str_contains($actTarget, 'client') || str_contains($slug, 'client')) {
                    $requiredQuota = QuotaKey::Clients->value;
                } elseif (str_contains($actUrl, 'invoice') || str_contains($actTarget, 'invoice') || str_contains($slug, 'invoice')) {
                    $requiredQuota = QuotaKey::Invoices->value;
                } elseif (str_contains($actUrl, 'booking') || str_contains($actTarget, 'booking') || str_contains($slug, 'booking')) {
                    $requiredQuota = QuotaKey::Appointments->value;
                }
            }

            $isCore = !empty($cs['is_core']) || in_array($slug, ['complete_kyc', 'configure_domain', 'configure_branding', 'select_package'], true);

            $stepData = new StepData(
                slug: (string)$cs['slug'],
                journey: OnboardingJourney::CRM_SETUP->value,
                title: (string)($cs['title'] ?? $cs['slug']),
                description: (string)($cs['description'] ?? ''),
                routeAction: 'user.dashboard',
                icon: (string)($cs['icon'] ?? 'check-circle'),
                requiredModule: !empty($cs['required_module']) ? (string)$cs['required_module'] : null,
                requiredQuota: $requiredQuota,
                dependsOn: !empty($cs['depends_on']) ? (string)$cs['depends_on'] : null,
                completionTriggerModel: $completionTriggerModel,
                sortOrder: (int)($cs['sort_order'] ?? 0),
                badge: !empty($cs['badge']) ? (string)$cs['badge'] : null,
                isRequired: $isCore ? true : !empty($cs['is_required']),
                actionType: $actionType,
                actionTarget: !empty($cs['action_target']) ? (string)$cs['action_target'] : null,
                actionUrl: !empty($cs['action_url']) ? (string)$cs['action_url'] : null,
                isCore: $isCore,
                canManualComplete: $canManualComplete,
            );

            // Filter if requires module that tenant doesn't have
            if ($stepData->requiredModule && !in_array(strtolower($stepData->requiredModule), $activeModules, true)) {
                continue;
            }

            $dynamicSteps[] = $stepData;
            $this->steps[$stepData->slug] = $stepData;

            if ($stepData->completionTriggerModel) {
                $modelClass = ltrim($stepData->completionTriggerModel, '\\');
                if (!isset($this->modelTriggers[$modelClass])) {
                    $this->modelTriggers[$modelClass] = [];
                }
                if (!in_array($stepData->slug, $this->modelTriggers[$modelClass], true)) {
                    $this->modelTriggers[$modelClass][] = $stepData->slug;
                }
            }
        }

        uasort($dynamicSteps, fn(StepData $a, StepData $b) => $a->sortOrder <=> $b->sortOrder);

        $defaultJourney = $this->getJourney(OnboardingJourney::CRM_SETUP->value) ?? OnboardingJourney::CRM_SETUP->toJourneyData();
        $journeyCopy = new JourneyData(
            slug: $defaultJourney->slug,
            title: $defaultJourney->title,
            description: $defaultJourney->description,
            icon: $defaultJourney->icon,
            requiredModule: null,
            sortOrder: 1,
            steps: array_values($dynamicSteps)
        );

        return [$journeyCopy->slug => $journeyCopy];
    }

    /**
     * Filter steps accessible to the given tenant.
     *
     * @return array<string, StepData>
     */
    public function getStepsForTenant(?Tenant $tenant = null): array
    {
        $journeys = $this->getJourneysForTenant($tenant);
        $steps = [];
        foreach ($journeys as $journey) {
            foreach ($journey->steps as $step) {
                $steps[$step->slug] = $step;
            }
        }
        uasort($steps, fn(StepData $a, StepData $b) => $a->sortOrder <=> $b->sortOrder);
        return $steps;
    }

    /**
     * Returns all registered completion model classes and their mapped step slugs.
     *
     * @return array<string, array<string>>
     */
    public function getAllCompletionModels(): array
    {
        return $this->modelTriggers;
    }

    /**
     * Get step slugs bound to a specific model.
     *
     * @return array<string>
     */
    public function getStepSlugsForModel(string $modelClass): array
    {
        $normalized = ltrim($modelClass, '\\');
        return $this->modelTriggers[$normalized] ?? [];
    }
}
