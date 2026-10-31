<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Enums\OrderProvisionStatus;
use Modules\ResellerHub\App\Enums\OrderStatus;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Presentation\OrderPresenter;
use Modules\ResellerHub\App\Services\Orders\OrderNumberGenerator;
use Modules\ResellerHub\App\Services\Orders\RenewalPeriodResolver;
use Modules\ResellerHub\App\Services\SubAccounts\SubAccountResolver;
use Modules\ResellerHub\App\Support\CurrencyContext;
use Modules\ResellerHub\App\Support\Money;
use Throwable;

/**
 * @property int $id
 * @property int $reseller_id
 * @property int|null $invoice_id
 * @property int|null $tenant_id
 * @property string $order_number
 * @property string $sub_account_name
 * @property string|null $sub_account_mobile
 * @property string|null $sub_account_email
 * @property string $status
 * @property string $billing_cycle
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $renewal_date
 * @property int $total_amount
 * @property int|null $renewal_price
 * @property string|null $renewal_price_type
 * @property array|null $modules_snapshot
 * @property string $provisioning_status
 * @property string|null $notes
 * @property int|null $created_by
 * @property-read Tenant|null $tenant
 * @property-read ResellerInvoice|null $invoice
 *
 * @method static Builder|ResellerOrder query()
 * @method static ResellerOrder|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerOrder findOrFail(mixed $id, array $columns = ['*'])
 * @method static ResellerOrder create(array $attributes = [])
 * @method static Builder|ResellerOrder where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder|ResellerOrder whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static Builder|ResellerOrder whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static Builder|ResellerOrder whereNull(string|array $columns, string $boolean = 'and', bool $not = false)
 * @method static Builder|ResellerOrder with(mixed $relations)
 * @method static Builder|ResellerOrder latest(string $column = 'created_at')
 */
class ResellerOrder extends Model
{
    use SoftDeletes;

    protected $table = 'reseller_orders';

    public const STATUS_PENDING = OrderStatus::PENDING->value;

    public const STATUS_ACTIVE = OrderStatus::ACTIVE->value;

    public const STATUS_INACTIVE = OrderStatus::INACTIVE->value;

    public const STATUS_CANCELLED = OrderStatus::CANCELLED->value;

    // Legacy aliases
    public const STATUS_SUSPENDED = OrderStatus::SUSPENDED->value;

    public const STATUS_COMPLETED = OrderStatus::COMPLETED->value;

    public const PROVISION_NOT = OrderProvisionStatus::NOT_PROVISIONED->value;

    public const PROVISION_DONE = OrderProvisionStatus::PROVISIONED->value;

    public const PROVISION_FAILED = OrderProvisionStatus::FAILED->value;

    protected $fillable = [
        'reseller_id',
        'invoice_id',
        'tenant_id',
        'is_custom_package',
        'source',
        'currency',
        'order_number',
        'sub_account_name',
        'sub_account_mobile',
        'sub_account_email',
        'status',
        'billing_cycle',
        'start_date',
        'end_date',
        'renewal_date',
        'total_amount',
        'contract_value',
        'renewal_price',
        'renewal_price_type',
        'modules_snapshot',
        'provisioning_status',
        'notes',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $order): void {
            CurrencyContext::snapshot($order);
        });
    }

    protected $casts = [
        'modules_snapshot' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ResellerInvoice::class, 'invoice_id');
    }

    /**
     * Invoices that belong to this order through the deterministic foreign key.
     * Now it is a plain FK relationship and can be eager loaded safely. Use
     * {@see resolveRelatedInvoices()} when you need the legacy union.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(ResellerInvoice::class, 'source_order_id', 'id')->latest('id');
    }

    public function resolveRelatedInvoices(array $with = ['items', 'payments']): Collection
    {
        $invoices = $this->relationLoaded('invoices')
            ? $this->getRelation('invoices')
            : $this->invoices()->with($with)->get();

        $collected = collect($invoices)->keyBy('id');

        if (! empty($this->invoice_id)) {
            $legacy = ResellerInvoice::with($with)
                ->where('tenant_id', $this->tenant_id)
                ->where(function ($q) {
                    $q->where('id', $this->invoice_id)
                        ->orWhere('parent_invoice_id', $this->invoice_id);
                })
                ->get();

            foreach ($legacy as $invoice) {
                $collected->put($invoice->id, $invoice);
            }
        }

        if (! empty($this->id)) {
            $byMeta = ResellerInvoice::with($with)
                ->where('tenant_id', $this->tenant_id)
                ->where(function ($q) {
                    $q->whereJsonContains('meta->source_order_id', (int) $this->id)
                        ->orWhereJsonContains('meta->source_order_id', (string) $this->id);
                })
                ->get();

            foreach ($byMeta as $invoice) {
                $collected->put($invoice->id, $invoice);
            }
        }

        if ($this->relationLoaded('invoice') && $this->invoice) {
            $collected->put($this->invoice->id, $this->invoice);
        }

        // Return an Eloquent collection (not a base support collection) so callers
        // keep the model-aware helpers they would get from a relationship.
        return (new ResellerInvoice)->newCollection(
            $collected->sortByDesc('id')->values()->all()
        );
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subAccount(): HasOneThrough
    {
        return $this->hasOneThrough(
            ResellerSubAccount::class,
            Tenant::class,
            'id',
            'tenant_id',
            'tenant_id',
            'id'
        );
    }

    /**
     * Resolve the Sub-Account model associated with this order (via tenant or contact info).
     */
    public function getSubAccountModel(): ?ResellerSubAccount
    {
        return app(SubAccountResolver::class)->resolve($this);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }

    public function isCustomPackage(): bool
    {
        if ($this->is_custom_package) {
            return true;
        }

        if (! empty($this->meta['is_custom']) || ! empty($this->meta['is_item_addition'])) {
            return true;
        }

        if ($this->tenant && empty($this->tenant->package_id)) {
            return true;
        }

        $snapshot = $this->modules_snapshot ?? [];
        if (collect($snapshot)->contains(fn ($m) => ! empty($m['is_custom_add']) || ! empty($m['is_custom_remove']))) {
            return true;
        }

        return false;
    }

    public function getPackageAttribute(): ?ResellerPackage
    {
        if ($this->isCustomPackage()) {
            return null;
        }

        if ($this->tenant && $this->tenant->package_id) {
            return $this->tenant->package ?? ResellerPackage::find($this->tenant->package_id);
        }

        return $this->invoice?->package;
    }

    public function isActive(): bool
    {
        return in_array($this->status_enum, [OrderStatus::ACTIVE, OrderStatus::COMPLETED], true);
    }

    public function isPending(): bool
    {
        return $this->status_enum === OrderStatus::PENDING;
    }

    public function isInactive(): bool
    {
        return in_array($this->status_enum, [OrderStatus::INACTIVE, OrderStatus::SUSPENDED], true);
    }

    public function isCancelled(): bool
    {
        return $this->status_enum === OrderStatus::CANCELLED;
    }

    public function canTransitionTo(OrderStatus|string $target): bool
    {
        $current = $this->status_enum ?? OrderStatus::fromOrDefault((string) $this->status);
        $targetEnum = $target instanceof OrderStatus ? $target : OrderStatus::fromOrDefault((string) $target);

        if (! $current || ! $targetEnum) {
            return true;
        }

        return $current->canTransitionTo($targetEnum);
    }

    public function transitionTo(OrderStatus|string $newStatus, bool $save = true): self
    {
        $targetEnum = $newStatus instanceof OrderStatus ? $newStatus : OrderStatus::fromOrDefault((string) $newStatus);
        if (! $targetEnum) {
            throw new InvalidArgumentException("وضعیت سفارش نامعتبر است: {$newStatus}");
        }

        if (! $this->canTransitionTo($targetEnum)) {
            $fromLabel = $this->status_label ?? $this->status;
            $toLabel = $targetEnum->label();
            throw new DomainException("امکان تغییر وضعیت سفارش از '{$fromLabel}' به '{$toLabel}' وجود ندارد.");
        }

        $this->status = $targetEnum->value;

        if ($save) {
            $this->save();
        }

        return $this;
    }

    public function isProvisioned(): bool
    {
        return $this->provisioning_status === self::PROVISION_DONE;
    }

    public function getStatusEnumAttribute(): ?OrderStatus
    {
        return app(OrderPresenter::class)->statusEnum($this);
    }

    public function getStatusLabelAttribute(): string
    {
        return app(OrderPresenter::class)->statusLabel($this);
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return app(OrderPresenter::class)->statusBadgeClasses($this);
    }

    public function getProvisioningStatusEnumAttribute(): ?OrderProvisionStatus
    {
        return app(OrderPresenter::class)->provisioningStatusEnum($this);
    }

    public function getProvisioningStatusLabelAttribute(): string
    {
        return app(OrderPresenter::class)->provisioningLabel($this);
    }

    public function getStatusBadgeAttribute(): array
    {
        return app(OrderPresenter::class)->statusBadge($this);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return app(OrderPresenter::class)->statusBadgeColor($this);
    }

    public function getBarColorAttribute(): string
    {
        return app(OrderPresenter::class)->barColor($this);
    }

    public function getIconBoxClassAttribute(): string
    {
        return app(OrderPresenter::class)->iconBoxClass($this);
    }

    public function isOnlinePurchase(): bool
    {
        if ($this->relationLoaded('invoice') && $this->invoice) {
            if ($this->invoice->isOnlinePurchase()) {
                return true;
            }
        } elseif ($this->invoice_id) {
            $invoice = ResellerInvoice::find($this->invoice_id);
            if ($invoice && $invoice->isOnlinePurchase()) {
                return true;
            }
        }

        $subAccount = $this->getSubAccountModel();
        if ($subAccount && $subAccount->isOnlinePurchase()) {
            return true;
        }

        return false;
    }

    public function getSourceLabelAttribute(): string
    {
        return app(OrderPresenter::class)->sourceLabel($this);
    }

    public function getSourceBadgeAttribute(): string
    {
        return app(OrderPresenter::class)->sourceBadge($this);
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereHas('invoice', fn (Builder $iq) => $iq->online())
                ->orWhereHas('tenant.subAccount', function (Builder $sq) {
                    $sq->whereIn('settings->source', ResellerHelper::ONLINE_SOURCES)
                        ->orWhere('settings->source', 'like', 'portal_%')
                        ->orWhere('settings->source', 'like', 'online_%')
                        ->orWhere('settings->source', 'like', 'dashboard_%')
                        ->orWhere('settings->source', 'like', 'website_%');
                });
        });
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereDoesntHave('invoice', fn (Builder $iq) => $iq->online())
                ->whereDoesntHave('tenant.subAccount', function (Builder $sq) {
                    $sq->whereIn('settings->source', ResellerHelper::ONLINE_SOURCES)
                        ->orWhere('settings->source', 'like', 'portal_%')
                        ->orWhere('settings->source', 'like', 'online_%')
                        ->orWhere('settings->source', 'like', 'dashboard_%')
                        ->orWhere('settings->source', 'like', 'website_%');
                });
        });
    }

    public function getProvisioningLabelAttribute(): string
    {
        return match ($this->provisioning_status) {
            self::PROVISION_NOT => 'سامانه تحویل داده نشده',
            self::PROVISION_DONE => 'سامانه متصل و فعال',
            self::PROVISION_FAILED => 'خطا در تحویل',
            default => $this->provisioning_status,
        };
    }

    public function getJalaliStartDateAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->start_date);
    }

    public function getJalaliEndDateAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->end_date);
    }

    public function getJalaliRenewalDateAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->renewal_date);
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return app(OrderPresenter::class)->billingCycleLabel($this);
    }

    public function getJalaliCreatedAtAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->created_at);
    }

    public function getJalaliExpiresAtAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->end_date ?: $this->renewal_date);
    }

    public function getJalaliProvisionedAtAttribute(): ?string
    {
        return $this->provisioning_status === self::PROVISION_DONE && $this->updated_at
            ? JalaliDateHelper::formatDate($this->updated_at)
            : null;
    }

    public function getSubAccountPhoneAttribute(): ?string
    {
        return $this->sub_account_mobile;
    }

    /**
     * Resolve billing cycle to month count (SSOT via BillingCycle enum).
     */
    public static function cycleToMonths(string|int|null $cycle): int
    {
        return BillingCycle::resolveMonthFromPeriodKey($cycle);
    }

    /**
     * @return array{cycle: string, months: int, days: int|null, is_custom: bool, label: string}
     */
    public function renewalPeriod(): array
    {
        return app(RenewalPeriodResolver::class)->resolve($this);
    }

    public function getRenewalPeriodAttribute(): array
    {
        return $this->renewalPeriod();
    }

    public function getCurrencyAttribute(): string
    {
        return (string) ($this->attributes['currency'] ?? CurrencyContext::forReseller((int) $this->reseller_id));
    }

    public function money(string $attribute = 'total_amount'): Money
    {
        return Money::of($this->getAttribute($attribute), $this->currency);
    }

    public static function generateNextNumber(int $resellerId): string
    {
        return app(OrderNumberGenerator::class)->generate($resellerId);
    }
}
