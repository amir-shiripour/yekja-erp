<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Onboarding;

use App\Models\CustomUserField;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserCustomValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Entities\Client;
use Modules\ResellerHub\App\Data\Onboarding\StepData;
use Modules\ResellerHub\App\Enums\OnboardingStep;
use Modules\ResellerHub\App\Enums\OnboardingStepPreset;
use Modules\ResellerHub\App\Enums\OnboardingTourStatus;
use Modules\ResellerHub\App\Events\TenantOnboardingStepCompleted;
use Modules\ResellerHub\App\Models\TenantOnboardingState;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Carbon\Carbon;
use Modules\ResellerHub\App\Services\TenantQuotaService;
use Throwable;

class OnboardingStateService
{
    public const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        protected OnboardingRegistry $registry
    ) {}

    /**
     * Cache key for a specific tenant's onboarding states.
     */
    protected function getCacheKey(int $tenantId): string
    {
        return "tenant_onboarding:{$tenantId}";
    }

    /**
     * Retrieve all onboarding states for a tenant (cached).
     *
     * @return array<string, array{status: string, completed_at: ?string, data: ?array}>
     */
    public function getTenantStates(int $tenantId): array
    {
        return Cache::remember($this->getCacheKey($tenantId), self::CACHE_TTL_SECONDS, function () use ($tenantId) {
            return TenantOnboardingState::forTenant($tenantId)
                ->get()
                ->mapWithKeys(fn(TenantOnboardingState $state) => [
                    $state->step_slug => [
                        'status' => $state->status,
                        'completed_at' => $state->completed_at?->toIso8601String(),
                        'data' => $state->data,
                    ],
                ])
                ->toArray();
        });
    }

    /**
     * Mark an onboarding step as completed.
     */
    public function markCompleted(int $tenantId, string $stepSlug, ?array $data = null): bool
    {
        try {
            $step = $this->registry->getStep($stepSlug);
            $journeySlug = $step?->journey ?? 'core';

            $state = TenantOnboardingState::firstOrNew([
                'tenant_id' => $tenantId,
                'step_slug' => $stepSlug,
            ]);

            $isAlreadyCompleted = $state->exists && $state->status === TenantOnboardingState::STATUS_COMPLETED;

            $state->journey_slug = $journeySlug;
            $state->status = TenantOnboardingState::STATUS_COMPLETED;
            if (!$state->completed_at) {
                $state->completed_at = now();
            }
            if ($data !== null) {
                $state->data = array_merge($state->data ?? [], $data);
            }
            $state->save();

            // Clear tenant cache
            $this->flushCache($tenantId);

            // Dispatch completion event if newly completed
            if (!$isAlreadyCompleted) {
                event(new TenantOnboardingStepCompleted($tenantId, $stepSlug, $data));
            }

            return true;
        } catch (Throwable $e) {
            Log::error("Failed to mark onboarding step {$stepSlug} as completed for tenant {$tenantId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark an onboarding step as skipped.
     */
    public function markSkipped(int $tenantId, string $stepSlug): bool
    {
        try {
            $step = $this->registry->getStep($stepSlug);
            if ($step && $step->isCore) {
                Log::warning("Attempted to skip core onboarding step {$stepSlug} for tenant {$tenantId}. Action rejected.");
                return false;
            }

            $journeySlug = $step?->journey ?? 'core';

            TenantOnboardingState::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'step_slug' => $stepSlug,
                ],
                [
                    'journey_slug' => $journeySlug,
                    'status' => TenantOnboardingState::STATUS_SKIPPED,
                ]
            );

            $this->flushCache($tenantId);
            return true;
        } catch (Throwable $e) {
            Log::error("Failed to mark onboarding step {$stepSlug} as skipped for tenant {$tenantId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a custom or modular step is auto-verifiable via database inspection.
     */
    public function isStepAutoVerifiable(StepData $step): bool
    {
        if ($step->isCore || $step->completionTriggerModel) {
            return true;
        }

        $slug = strtolower($step->slug);
        $preset = OnboardingStepPreset::tryFrom($slug);
        if ($preset) {
            return $preset->triggerModel() !== null;
        }

        $actUrl = strtolower($step->actionUrl ?? '');
        $actTarget = strtolower($step->actionTarget ?? '');

        return str_contains($actUrl, 'client') || str_contains($slug, 'client') || str_contains($actTarget, 'client')
            || str_contains($actUrl, 'user') || str_contains($slug, 'user') || str_contains($slug, 'team') || str_contains($actTarget, 'team')
            || str_contains($actUrl, 'invoice') || str_contains($slug, 'invoice') || str_contains($actTarget, 'invoice')
            || str_contains($actUrl, 'booking') || str_contains($slug, 'booking') || str_contains($actTarget, 'booking')
            || str_contains($actUrl, 'sms') || str_contains($slug, 'sms') || str_contains($actTarget, 'sms')
            || str_contains($actUrl, 'wallet') || str_contains($slug, 'wallet') || str_contains($actTarget, 'wallet');
    }

    /**
     * Check database to see if the action required by this step has actually been performed.
     */
    public function verifyStepEntityExistence(Tenant $tenant, StepData $step, ?User $tenantUser = null): bool
    {
        $tenantId = $tenant->id;
        $actUrl = strtolower($step->actionUrl ?? '');
        $slug = strtolower($step->slug);
        $actTarget = strtolower($step->actionTarget ?? '');
        $triggerModel = $step->completionTriggerModel;

        // 1. Explicit / Inferred Model Trigger
        if ($triggerModel) {
            if ($triggerModel === User::class) {
                return User::where('tenant_id', $tenantId)->count() > 1;
            }
            if (is_subclass_of($triggerModel, Model::class) || class_exists($triggerModel)) {
                try {
                    return $triggerModel::where('tenant_id', $tenantId)->exists();
                } catch (Throwable $e) {
                }
            }
        }

        // 2. Client / Contact Module
        if (str_contains($actUrl, 'client') || str_contains($slug, 'client') || str_contains($actTarget, 'client')) {
            if (class_exists(Client::class)) {
                return Client::where('tenant_id', $tenantId)->exists();
            }
        }

        // 3. Team Member / Staff User Module
        if (str_contains($actUrl, 'user') || str_contains($slug, 'user') || str_contains($slug, 'team') || str_contains($actTarget, 'team')) {
            return User::where('tenant_id', $tenantId)->count() > 1;
        }

        // 4. Invoices / Accounting Module
        if (str_contains($actUrl, 'invoice') || str_contains($slug, 'invoice') || str_contains($actTarget, 'invoice')) {
            $hasAccounting = Schema::hasTable('accounting_invoices')
                && DB::table('accounting_invoices')->where('tenant_id', $tenantId)->exists();
            $hasService = Schema::hasTable('service_invoices')
                && DB::table('service_invoices')->where('tenant_id', $tenantId)->exists();
            return $hasAccounting || $hasService;
        }

        // 5. Booking / Appointments Module
        if (str_contains($actUrl, 'booking') || str_contains($slug, 'booking') || str_contains($actTarget, 'booking')) {
            $hasAppt = Schema::hasTable('appointments')
                && DB::table('appointments')->where('tenant_id', $tenantId)->exists();
            $hasSvc = Schema::hasTable('booking_services')
                && DB::table('booking_services')->where('tenant_id', $tenantId)->exists();
            return $hasAppt || $hasSvc;
        }

        // 6. SMS Settings Module
        if (str_contains($actUrl, 'sms') || str_contains($slug, 'sms') || str_contains($actTarget, 'sms')) {
            $hasSmsGateway = Schema::hasTable('sms_gateway_settings')
                && $tenantUser && DB::table('sms_gateway_settings')->where('user_id', $tenantUser->id)->exists();
            $hasSmsSettings = Schema::hasTable('sms_settings')
                && DB::table('sms_settings')->whereNotNull('api_key')->exists();
            return $hasSmsGateway || $hasSmsSettings;
        }

        // 7. Wallet / Gateway Module
        if (str_contains($actUrl, 'wallet') || str_contains($slug, 'wallet') || str_contains($actTarget, 'wallet')) {
            if (Schema::hasTable('wallets')) {
                $hasPositive = DB::table('wallets')
                    ->where(function ($q) use ($tenantId, $tenantUser) {
                        if ($tenantUser) {
                            $q->where('holder_id', $tenantUser->id);
                        }
                        $q->orWhere('holder_id', $tenantId);
                    })
                    ->where('balance', '>', 0)
                    ->exists();
                if ($hasPositive) {
                    return true;
                }
            }
            if (Schema::hasTable('wallet_transactions')) {
                return DB::table('wallet_transactions')
                    ->where('tenant_id', $tenantId)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * Revert / remove an onboarding step state for a tenant.
     */
    public function revertStep(int $tenantId, string $stepSlug): bool
    {
        try {
            TenantOnboardingState::where('tenant_id', $tenantId)
                ->where('step_slug', $stepSlug)
                ->delete();

            $this->flushCache($tenantId);
            return true;
        } catch (Throwable $e) {
            Log::error("Failed to revert onboarding step {$stepSlug} for tenant {$tenantId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if any onboarding steps are listening for creation of this model and complete them.
     */
    public function checkAndCompleteForModel(Model $model): void
    {
        $tenantId = $model->tenant_id ?? null;
        if (!$tenantId) {
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $journeys = $this->registry->getJourneysForTenant($tenant);
        foreach ($journeys as $journey) {
            foreach ($journey->steps as $step) {
                if ($step->completionTriggerModel && is_a($model, $step->completionTriggerModel)) {
                    $this->markCompleted($tenantId, $step->slug, [
                        'source' => 'model_trigger',
                        'model' => get_class($model),
                        'id' => $model->getKey(),
                    ]);
                }
            }
        }
    }

    /**
     * Check if a specific step is completed by tenant.
     */
    public function isStepCompleted(int $tenantId, string $stepSlug): bool
    {
        $states = $this->getTenantStates($tenantId);
        return isset($states[$stepSlug]) && $states[$stepSlug]['status'] === TenantOnboardingState::STATUS_COMPLETED;
    }

    /**
     * Check if a specific step is completed or skipped by tenant.
     */
    public function isStepCompletedOrSkipped(int $tenantId, string $stepSlug): bool
    {
        $states = $this->getTenantStates($tenantId);
        return isset($states[$stepSlug]) && in_array($states[$stepSlug]['status'], [
            TenantOnboardingState::STATUS_COMPLETED,
            TenantOnboardingState::STATUS_SKIPPED,
        ], true);
    }

    /**
     * Check if a tenant can access/execute a step (pre-requisites satisfied).
     */
    public function canAccessStep(int $tenantId, string $stepSlug): bool
    {
        $step = $this->registry->getStep($stepSlug);
        if (!$step) {
            return false;
        }

        if (!$step->dependsOn) {
            return true;
        }

        return $this->isStepCompletedOrSkipped($tenantId, $step->dependsOn);
    }

    /**
     * Flush cache for a tenant.
     */
    public function flushCache(int $tenantId): void
    {
        Cache::forget($this->getCacheKey($tenantId));
    }

    /**
     * Check if all remaining (uncompleted / unskipped) steps for a tenant are optional.
     */
    public function areRemainingStepsOptional(Tenant|int $tenant): bool
    {
        $tenantModel = $tenant instanceof Tenant ? $tenant : Tenant::find($tenant);
        if (!$tenantModel || $tenantModel->isOnboardingCompleted()) {
            return false;
        }

        $progress = $this->getTenantProgress($tenantModel);
        if (empty($progress['journeys']) || empty($progress['total']) || !empty($progress['is_finished'])) {
            return false;
        }

        $steps = $progress['journeys'][0]['steps'] ?? [];
        $hasIncompleteRequired = false;
        $remainingOptionalCount = 0;

        foreach ($steps as $item) {
            $isDone = in_array($item['status'], [
                TenantOnboardingState::STATUS_COMPLETED,
                TenantOnboardingState::STATUS_SKIPPED,
            ], true);

            if (!$isDone) {
                if ($item['step']->isRequired) {
                    $hasIncompleteRequired = true;
                } else {
                    $remainingOptionalCount++;
                }
            }
        }

        return !$hasIncompleteRequired && ($remainingOptionalCount > 0);
    }

    /**
     * Determine if the tour is currently dismissed or in a postponed/snoozed state for a tenant.
     * Takes into account the reseller's optional tour reminder settings.
     */
    public function isTourDismissedOrPostponed(Tenant|int $tenant): bool
    {
        $tenantModel = $tenant instanceof Tenant ? $tenant : Tenant::find($tenant);
        if (!$tenantModel) {
            return false;
        }

        // If tenant's onboarding tour is already marked as completed, it remains permanently closed/dismissed
        if ($tenantModel->isOnboardingCompleted()) {
            return true;
        }

        $tourConfig = ResellerSetting::getOnboardingTourConfig($tenantModel->reseller_id);
        if (empty($tourConfig['enabled'])) {
            return true;
        }

        $tenantSettings = $tenantModel->settings ?? [];
        $postponedAt = $tenantSettings['onboarding_postponed_at'] ?? null;

        // If user never postponed optional steps:
        if (!$postponedAt) {
            return (bool)session('onboarding_widget_dismissed', false);
        }

        // Reminders for optional steps:
        $reminderEnabled = (bool)($tourConfig['optional_reminder_enabled'] ?? true);
        $intervalDays = (int)($tourConfig['optional_reminder_interval_days'] ?? 3);

        // If reminder is disabled or set to never (-1):
        if (!$reminderEnabled || $intervalDays === -1) {
            return true;
        }

        // If interval is 0 (every new session / login):
        if ($intervalDays === 0) {
            return (bool)session('onboarding_widget_dismissed', false);
        }

        // Check if intervalDays have passed since postponedAt:
        try {
            $postponedCarbon = Carbon::parse($postponedAt);
            $hoursPassed = $postponedCarbon->diffInHours(now());

            if ($hoursPassed >= ($intervalDays * 24)) {
                // Reminder interval has elapsed! Tour should pop up again.
                session()->forget('onboarding_widget_dismissed');
                return false;
            }

            return true;
        } catch (Throwable $e) {
            return (bool)session('onboarding_widget_dismissed', false);
        }
    }

    /**
     * Compute comprehensive onboarding progress for a tenant.
     *
     * @param Tenant|int $tenant
     * @param bool $persist Whether to persist auto-verified step transitions to database (default false for safe GET requests)
     * @return array{
     *     total: int,
     *     completed: int,
     *     skipped: int,
     *     percentage: int,
     *     is_finished: bool,
     *     next_step: ?StepData,
     *     journeys: array
     * }
     */
    public function getTenantProgress(Tenant|int $tenant, bool $persist = false): array
    {
        $tenantModel = $tenant instanceof Tenant ? $tenant : Tenant::find($tenant);
        if (!$tenantModel) {
            return [
                'total' => 0,
                'completed' => 0,
                'skipped' => 0,
                'percentage' => 0,
                'is_finished' => true,
                'next_step' => null,
                'journeys' => [],
                'status' => OnboardingTourStatus::COMPLETED->value,
            ];
        }

        if ($tenantModel->isOnboardingCompleted()) {
            $states = $this->getTenantStates($tenantModel->id);
            $count = max(count($states), 1);
            return [
                'total' => $count,
                'completed' => $count,
                'skipped' => 0,
                'percentage' => 100,
                'is_finished' => true,
                'next_step' => null,
                'journeys' => [],
                'status' => OnboardingTourStatus::COMPLETED->value,
                'completed_at' => data_get($tenantModel->settings, 'onboarding_completed_at'),
            ];
        }

        $applicableJourneys = $this->registry->getJourneysForTenant($tenantModel);
        if (empty($applicableJourneys)) {
            return [
                'total' => 0,
                'completed' => 0,
                'skipped' => 0,
                'percentage' => 100,
                'is_finished' => true,
                'next_step' => null,
                'journeys' => [],
            ];
        }

        $activeStepSlugs = [];
        foreach ($applicableJourneys as $j) {
            foreach ($j->steps as $s) {
                $activeStepSlugs[] = $s->slug;
            }
        }

        $states = $this->getTenantStates($tenantModel->id);

        $applyStepCompletion = function (string $slug, array $meta = []) use (&$states, $tenantModel, $persist) {
            if ($persist) {
                $this->markCompleted($tenantModel->id, $slug, $meta);
                $states = $this->getTenantStates($tenantModel->id);
            } else {
                $states[$slug] = [
                    'status' => TenantOnboardingState::STATUS_COMPLETED,
                    'completed_at' => now()->toIso8601String(),
                    'meta' => $meta,
                ];
            }
        };

        $applyStepReversion = function (string $slug) use (&$states, $tenantModel, $persist) {
            if ($persist) {
                $this->revertStep($tenantModel->id, $slug);
                $states = $this->getTenantStates($tenantModel->id);
            } else {
                unset($states[$slug]);
            }
        };

        if (in_array(OnboardingStep::COMPLETE_KYC->value, $activeStepSlugs, true)) {
            $subAccountFields = CustomUserField::forSubAccounts()->get();
            if ($subAccountFields->isEmpty()) {
                if (!isset($states[OnboardingStep::COMPLETE_KYC->value]) || $states[OnboardingStep::COMPLETE_KYC->value]['status'] !== TenantOnboardingState::STATUS_COMPLETED) {
                    $applyStepCompletion(OnboardingStep::COMPLETE_KYC->value, ['source' => 'no_fields_required']);
                }
            } else {
                /** @var User|null $tenantUser */
                $tenantUser = User::where('tenant_id', $tenantModel->id)->first();
                if (!$tenantUser && $tenantModel->sub_account_id && $tenantModel->subAccount) {
                    $tenantUser = $tenantModel->subAccount->user;
                }

                if ($tenantUser) {
                    $requiredFieldNames = $subAccountFields->where('is_required', true)->pluck('field_name')->toArray();
                    if (empty($requiredFieldNames)) {
                        $requiredFieldNames = $subAccountFields->pluck('field_name')->toArray();
                    }

                    $existingValuesCount = UserCustomValue::where('user_id', $tenantUser->id)
                        ->whereIn('field_name', $requiredFieldNames)
                        ->whereNotNull('value')
                        ->where('value', '!=', '')
                        ->count();

                    if ($existingValuesCount >= count($requiredFieldNames) && count($requiredFieldNames) > 0) {
                        if (!isset($states[OnboardingStep::COMPLETE_KYC->value]) || $states[OnboardingStep::COMPLETE_KYC->value]['status'] !== TenantOnboardingState::STATUS_COMPLETED) {
                            $applyStepCompletion(OnboardingStep::COMPLETE_KYC->value, [
                                'source' => 'auto_verified',
                                'user_id' => $tenantUser->id,
                            ]);
                        }
                    }
                }
            }
        }

        // Auto-verify 'configure_domain' if custom domain is set or user explicitly configured their domain
        if (in_array(OnboardingStep::CONFIGURE_DOMAIN->value, $activeStepSlugs, true)) {
            if (!isset($states[OnboardingStep::CONFIGURE_DOMAIN->value]) || $states[OnboardingStep::CONFIGURE_DOMAIN->value]['status'] !== TenantOnboardingState::STATUS_COMPLETED) {
                if (!empty($tenantModel->custom_domain) || !empty($tenantModel->settings['domain_configured'])) {
                    $applyStepCompletion(OnboardingStep::CONFIGURE_DOMAIN->value, [
                        'source' => 'auto_verified',
                        'domain' => $tenantModel->custom_domain ?: $tenantModel->subdomain,
                    ]);
                }
            }
        }

        // Auto-verify 'configure_branding' if custom branding is saved
        if (in_array(OnboardingStep::CONFIGURE_BRANDING->value, $activeStepSlugs, true)) {
            if (!isset($states[OnboardingStep::CONFIGURE_BRANDING->value]) || $states[OnboardingStep::CONFIGURE_BRANDING->value]['status'] !== TenantOnboardingState::STATUS_COMPLETED) {
                if (!empty($tenantModel->branding['is_custom']) || !empty($tenantModel->settings['branding_configured'])) {
                    $applyStepCompletion(OnboardingStep::CONFIGURE_BRANDING->value, [
                        'source' => 'auto_verified',
                    ]);
                }
            }
        }

        // Auto-verify 'select_package' if tenant has an active paid plan or active completed order
        if (in_array(OnboardingStep::SELECT_PACKAGE->value, $activeStepSlugs, true)) {
            if (!isset($states[OnboardingStep::SELECT_PACKAGE->value]) || $states[OnboardingStep::SELECT_PACKAGE->value]['status'] !== TenantOnboardingState::STATUS_COMPLETED) {
                if ($tenantModel->hasActivePaidPlan()) {
                    $applyStepCompletion(OnboardingStep::SELECT_PACKAGE->value, [
                        'source' => 'auto_verified',
                        'package_id' => $tenantModel->package_id,
                    ]);
                }
            }
        }

        /** @var User|null $tenantUser */
        $tenantUser = User::where('tenant_id', $tenantModel->id)->first();
        if (!$tenantUser && $tenantModel->sub_account_id && $tenantModel->subAccount) {
            $tenantUser = $tenantModel->subAccount->user;
        }

        // Auto-verify and reconcile custom / modular steps
        foreach ($applicableJourneys as $j) {
            foreach ($j->steps as $step) {
                if ($step->isCore) {
                    continue;
                }

                if ($this->isStepAutoVerifiable($step)) {
                    $entityExists = $this->verifyStepEntityExistence($tenantModel, $step, $tenantUser);
                    $isCurrentlyCompleted = isset($states[$step->slug]) && $states[$step->slug]['status'] === TenantOnboardingState::STATUS_COMPLETED;

                    if ($entityExists) {
                        if (!$isCurrentlyCompleted) {
                            $applyStepCompletion($step->slug, [
                                'source' => 'auto_verified',
                            ]);
                        }
                    } elseif (!$step->canManualComplete && $isCurrentlyCompleted) {

                        $applyStepReversion($step->slug);
                    }
                }
            }
        }

        $totalSteps = 0;
        $completedSteps = 0;
        $skippedSteps = 0;
        $nextStep = null;
        $journeysData = [];

        foreach ($applicableJourneys as $journeySlug => $journey) {
            $journeyTotal = 0;
            $journeyCompleted = 0;
            $stepsWithStatus = [];

            foreach ($journey->steps as $step) {
                $journeyTotal++;
                $totalSteps++;

                $stateInfo = $states[$step->slug] ?? null;
                $status = $stateInfo['status'] ?? TenantOnboardingState::STATUS_PENDING;

                if ($status === TenantOnboardingState::STATUS_COMPLETED) {
                    $journeyCompleted++;
                    $completedSteps++;
                } elseif ($status === TenantOnboardingState::STATUS_SKIPPED) {
                    $skippedSteps++;
                }

                $isAccessible = !$step->dependsOn || (isset($states[$step->dependsOn]) && in_array($states[$step->dependsOn]['status'], [
                    TenantOnboardingState::STATUS_COMPLETED,
                    TenantOnboardingState::STATUS_SKIPPED,
                ], true));

                $isQuotaLocked = false;
                $quotaKeyStr = $step->requiredQuota;
                if ($quotaKeyStr && $tenantModel->reseller_id && !$tenantModel->hasActivePaidPlan()) {
                    try {
                        /** @var TenantQuotaService $quotaService */
                        $quotaService = app(TenantQuotaService::class);
                        if (!$quotaService->hasRemainingQuota($tenantModel, $quotaKeyStr)) {
                            $isQuotaLocked = true;
                        }
                    } catch (Throwable $e) {
                        $isQuotaLocked = true;
                    }
                }

                if (!$nextStep && $status === TenantOnboardingState::STATUS_PENDING && $isAccessible) {
                    $nextStep = $step;
                }

                $stepsWithStatus[] = [
                    'step' => $step,
                    'status' => $status,
                    'is_accessible' => $isAccessible,
                    'is_quota_locked' => $isQuotaLocked,
                    'required_quota' => $quotaKeyStr,
                    'completed_at' => $stateInfo['completed_at'] ?? null,
                ];
            }

            $journeysData[] = [
                'journey' => $journey,
                'total' => $journeyTotal,
                'completed' => $journeyCompleted,
                'percentage' => $journeyTotal > 0 ? (int)round(($journeyCompleted / $journeyTotal) * 100) : 100,
                'steps' => $stepsWithStatus,
            ];
        }

        $percentage = $totalSteps > 0 ? (int)round(($completedSteps / $totalSteps) * 100) : 100;
        $isFinished = ($totalSteps > 0) && ($completedSteps + $skippedSteps >= $totalSteps);

        if ($isFinished && !$tenantModel->isOnboardingCompleted()) {
            $tenantModel->setOnboardingStatus(OnboardingTourStatus::COMPLETED)->save();
            $this->flushCache($tenantModel->id);
        }

        return [
            'total' => $totalSteps,
            'completed' => $completedSteps,
            'skipped' => $skippedSteps,
            'percentage' => $percentage,
            'is_finished' => $isFinished,
            'next_step' => $nextStep,
            'journeys' => $journeysData,
            'status' => $tenantModel->getOnboardingStatus()->value,
        ];
    }

    /**
     * Explicitly synchronize and persist onboarding step auto-verifications to the database.
     * Use this in background jobs, console commands, or POST mutation actions rather than GET views.
     */
    public function syncTenantProgress(Tenant|int $tenant): array
    {
        return $this->getTenantProgress($tenant, persist: true);
    }
}
