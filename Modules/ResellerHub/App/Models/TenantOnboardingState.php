<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\Tenant;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ResellerHub\App\Enums\OnboardingStepStatus;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $step_slug
 * @property string|null $journey_slug
 * @property string $status
 * @property Carbon|null $completed_at
 * @property array|null $data
 * @property-read Tenant $tenant
 * @property-read OnboardingStepStatus|null $status_enum
 * @property-read string $status_label
 * @property-read string $status_badge
 * @method static Builder|TenantOnboardingState query()
 * @method static TenantOnboardingState|null find(mixed $id, array $columns = ['*'])
 * @method static TenantOnboardingState create(array $attributes = [])
 * @method static Builder|TenantOnboardingState where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder|TenantOnboardingState forTenant(int $tenantId)
 * @method static Builder|TenantOnboardingState completed()
 * @method static Builder|TenantOnboardingState pending()
 * @method static Builder|TenantOnboardingState skipped()
 */
class TenantOnboardingState extends Model
{
    public const STATUS_PENDING = OnboardingStepStatus::PENDING->value;
    public const STATUS_COMPLETED = OnboardingStepStatus::COMPLETED->value;
    public const STATUS_SKIPPED = OnboardingStepStatus::SKIPPED->value;

    protected $table = 'tenant_onboarding_states';

    protected $fillable = [
        'tenant_id',
        'step_slug',
        'journey_slug',
        'status',
        'completed_at',
        'data',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'data' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * @noinspection SqlResolve
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where($this->qualifyColumn('tenant_id'), $tenantId);
    }

    /**
     * @noinspection SqlResolve
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), self::STATUS_COMPLETED);
    }

    /**
     * @noinspection SqlResolve
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), self::STATUS_PENDING);
    }

    /**
     * @noinspection SqlResolve
     */
    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), self::STATUS_SKIPPED);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    public function getStatusEnumAttribute(): ?OnboardingStepStatus
    {
        return OnboardingStepStatus::fromOrDefault($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum?->label() ?? ($this->status ?: 'نامشخص');
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status_enum?->badgeClasses() ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }
}
