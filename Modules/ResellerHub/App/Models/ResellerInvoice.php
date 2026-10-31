<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Events\InvoiceStatusChanged;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Presentation\InvoicePresenter;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\Invoices\InvoiceNumberGenerator;
use Modules\ResellerHub\App\Services\Invoices\InvoiceOrderResolver;
use Modules\ResellerHub\App\Services\Invoices\InvoiceSettlementService;
use Modules\ResellerHub\App\Services\Invoices\InvoiceTotalsCalculator;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;
use Modules\ResellerHub\App\Services\SubAccounts\SubAccountResolver;
use Modules\ResellerHub\App\Support\CurrencyContext;
use Modules\ResellerHub\App\Support\Money;
use Modules\ResellerHub\App\Traits\HasInvoiceSourceScopes;

/**
 * @property int $id
 * @property int $reseller_id
 * @property int|null $tenant_id
 * @property int|null $parent_invoice_id
 * @property int|null $package_id
 * @property string|null $currency
 * @property string $invoice_number
 * @property string $type
 * @property string $status
 * @property string $sub_account_name
 * @property string|null $sub_account_mobile
 * @property string|null $sub_account_email
 * @property Carbon|null $issue_date
 * @property Carbon|null $due_date
 * @property int $subtotal
 * @property string $discount_type
 * @property int $discount_value
 * @property int $discount_amount
 * @property float $tax_percent
 * @property int $tax_amount
 * @property int $total
 * @property int $paid_amount
 * @property string|null $payment_method
 * @property string|null $transaction_ref
 * @property Carbon|null $paid_at
 * @property string|null $notes
 * @property string|null $terms
 * @property array|null $meta
 * @property int|null $created_by
 * @property-read Tenant|null $tenant
 * @property-read ResellerPackage|null $package
 * @property-read ResellerOrder|null $order
 * @property-read Collection|ResellerInvoicePayment[] $payments
 * @property-read Collection|ResellerInvoiceItem[] $items
 *
 * @method static Builder|ResellerInvoice query()
 * @method static ResellerInvoice|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerInvoice findOrFail(mixed $id, array $columns = ['*'])
 * @method static ResellerInvoice create(array $attributes = [])
 * @method static Builder|ResellerInvoice where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder|ResellerInvoice whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static Builder|ResellerInvoice whereNull(string|array $columns, string $boolean = 'and', bool $not = false)
 * @method static Builder|ResellerInvoice whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static Builder|ResellerInvoice with(mixed $relations)
 * @method static Builder|ResellerInvoice latest(string $column = 'created_at')
 */
class ResellerInvoice extends Model
{
    use HasInvoiceSourceScopes, SoftDeletes;

    protected $table = 'reseller_invoices';

    public const TYPE_OFFICIAL = InvoiceType::OFFICIAL->value;

    public const STATUS_DRAFT = InvoiceStatus::DRAFT->value;

    public const STATUS_UNPAID = InvoiceStatus::UNPAID->value;

    public const STATUS_PAID = InvoiceStatus::PAID->value;

    public const STATUS_OVERDUE = InvoiceStatus::OVERDUE->value;

    public const STATUS_CANCELLED = InvoiceStatus::CANCELLED->value;

    protected $fillable = [
        'reseller_id',
        'tenant_id',
        'source_order_id',
        'parent_invoice_id',
        'package_id',
        'currency',
        'invoice_number',
        'type',
        'source',
        'status',
        'sub_account_name',
        'sub_account_mobile',
        'sub_account_email',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'paid_amount',
        'payment_method',
        'transaction_ref',
        'paid_at',
        'notes',
        'terms',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'renewal_applied_at' => 'datetime',
    ];

    public function sourceOrder(): BelongsTo
    {
        return $this->belongsTo(ResellerOrder::class, 'source_order_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
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
            'id',        // Local key on tenants table that matches reseller_invoices.tenant_id
            'tenant_id', // Foreign key on reseller_sub_accounts table that matches tenants.id
            'tenant_id', // Foreign key on reseller_invoices table
            'id'         // Local key on tenants table
        );
    }

    public function getSubAccountModel(): ?ResellerSubAccount
    {
        return app(SubAccountResolver::class)->resolve($this);
    }


    public function customerUser(): ?User
    {
        return app(SubAccountResolver::class)->customerUser($this);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ResellerPackage::class, 'package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ResellerInvoiceItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ResellerInvoiceItem::class, 'invoice_id');
    }

    /**
     * @return HasMany<ResellerInvoicePayment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ResellerInvoicePayment::class, 'invoice_id');
    }

    /**
     * @return HasMany<ResellerInvoicePayment>
     */
    public function validPayments(): HasMany
    {
        return $this->hasMany(ResellerInvoicePayment::class, 'invoice_id')
            ->whereIn('status', ['paid', 'completed']);
    }

    /**
     * @return HasMany<ResellerInvoicePayment>
     */
    public function pendingPayments(): HasMany
    {
        return $this->hasMany(ResellerInvoicePayment::class, 'invoice_id')
            ->where('status', 'pending');
    }

    public function calculatePaidAmount(): int
    {
        return app(InvoiceSettlementService::class)->calculatePaidAmount($this);
    }

    public function order(): HasOne
    {
        return $this->hasOne(ResellerOrder::class, 'invoice_id');
    }

    /**
     * Every order linked to this invoice.
     * @return HasMany<ResellerOrder>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(ResellerOrder::class, 'invoice_id');
    }

    /**
     * Resolve the associated ResellerOrder.
     */
    public function getOrderModel(): ?ResellerOrder
    {
        return app(InvoiceOrderResolver::class)->resolve($this);
    }

    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_invoice_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'parent_invoice_id');
    }


    protected static function booted(): void
    {
        static::creating(static function (self $invoice): void {
            CurrencyContext::snapshot($invoice);
        });

        static::saving(static function (self $invoice): void {
            if (!empty($invoice->source)) {
                return;
            }

            $metaSource = $invoice->meta['source'] ?? null;

            if (!empty($metaSource)) {
                $invoice->source = (string)$metaSource;

                return;
            }

            if (!empty($invoice->meta['is_upgrade'])) {
                $invoice->source = 'upgrade';
            }
        });
    }


    public function isPaid(): bool
    {
        return app(InvoiceSettlementService::class)->isPaid($this);
    }

    public function isCancelled(): bool
    {
        return $this->status_enum === InvoiceStatus::CANCELLED;
    }

    public function isDraft(): bool
    {
        return $this->status_enum === InvoiceStatus::DRAFT;
    }

    public function isOverdueStatus(): bool
    {
        return $this->status_enum === InvoiceStatus::OVERDUE;
    }

    public function isItemAddition(): bool
    {
        return !empty($this->meta['is_item_addition']);
    }

    public function isUpgrade(): bool
    {
        if (!empty($this->meta['is_upgrade']) || $this->type === 'upgrade') {
            return true;
        }

        if ($this->relationLoaded('items')) {
            return $this->items->contains(fn($it) => $it->item_type === 'package_upgrade' || !empty($it->meta['is_upgrade_item']));
        }

        return $this->items()->where('item_type', 'package_upgrade')->exists();
    }

    public function isOverdue(): bool
    {
        if ($this->isPaid() || $this->isCancelled()) {
            return false;
        }

        if (empty($this->due_date)) {
            return false;
        }

        $gregorianDueDate = Carbon::parse($this->due_date);

        return $gregorianDueDate->isPast() && !$gregorianDueDate->isToday();
    }

    public function updatePaymentStatus(bool $save = true): self
    {
        return app(InvoiceSettlementService::class)->updatePaymentStatus($this, $save);
    }

    public function getStatusEnumAttribute(): ?InvoiceStatus
    {
        return app(InvoicePresenter::class)->statusEnum($this);
    }

    public function getStatusLabelAttribute(): string
    {
        return app(InvoicePresenter::class)->statusLabel($this);
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return app(InvoicePresenter::class)->statusBadgeClasses($this);
    }

    public function canTransitionTo(InvoiceStatus|string $target): bool
    {
        $current = $this->status_enum ?? InvoiceStatus::fromOrDefault((string)$this->status);
        $targetEnum = $target instanceof InvoiceStatus ? $target : InvoiceStatus::fromOrDefault((string)$target);

        if (!$current || !$targetEnum) {
            return true;
        }

        return $current->canTransitionTo($targetEnum);
    }

    public function transitionTo(InvoiceStatus|string $newStatus, bool $save = true): self
    {
        $targetEnum = $newStatus instanceof InvoiceStatus ? $newStatus : InvoiceStatus::fromOrDefault((string)$newStatus);
        if (!$targetEnum) {
            throw new \InvalidArgumentException("وضعیت فاکتور نامعتبر است: {$newStatus}");
        }

        if (!$this->canTransitionTo($targetEnum)) {
            $fromLabel = $this->status_label;
            $toLabel = $targetEnum->label();
            throw new \DomainException("امکان تغییر وضعیت فاکتور از '{$fromLabel}' به '{$toLabel}' وجود ندارد.");
        }

        $oldStatus = $this->status;
        $this->status = $targetEnum->value;

        if ($targetEnum === InvoiceStatus::PAID && !$this->paid_at) {
            $this->paid_at = now();
            $this->paid_amount = $this->total;
        }

        if ($save) {
            $this->save();
        }

        if ($oldStatus !== $this->status) {
            event(new InvoiceStatusChanged($this, $this->status, $oldStatus));
        }

        return $this;
    }

    public function getTypeEnumAttribute(): ?InvoiceType
    {
        return app(InvoicePresenter::class)->typeEnum($this);
    }

    public function getTypeLabelAttribute(): string
    {
        return app(InvoicePresenter::class)->typeLabel($this);
    }

    public function getPaidAmountAttribute(): int
    {
        if (array_key_exists('paid_amount', $this->attributes) && $this->attributes['paid_amount'] !== null) {
            return (int)$this->attributes['paid_amount'];
        }

        return $this->calculatePaidAmount();
    }

    public function remainingAmount(): int
    {
        return app(InvoiceSettlementService::class)->remainingAmount($this);
    }

    public function recordPayment(int $amount, string $method = 'direct', ?string $transactionId = null, ?string $notes = null, ?int $userId = null, ?string $idempotencyKey = null): ResellerInvoicePayment
    {
        return app(InvoiceSettlementService::class)->recordPayment(
            invoice: $this,
            amount: $amount,
            method: $method,
            transactionId: $transactionId,
            notes: $notes,
            userId: $userId,
            idempotencyKey: $idempotencyKey
        );
    }

    public function isRenewal(): bool
    {
        if ($this->isUpgrade() || $this->isItemAddition()) {
            return false;
        }

        return !empty($this->meta['is_renewal']) || ($this->parent_invoice_id !== null && empty($this->meta['is_upgrade']));
    }

    public function isInitialProvisioning(): bool
    {
        return !$this->isRenewal() && !$this->isUpgrade() && !$this->isItemAddition() && $this->parent_invoice_id === null;
    }

    public function isSystemGenerated(): bool
    {
        if ($this->isUpgrade() || $this->isItemAddition()) {
            return false;
        }

        return !empty($this->meta['created_by_workflow'])
            || ($this->isRenewal() && empty($this->meta['created_by_manual_renewal']));
    }

    public function isManualRenewal(): bool
    {
        return !empty($this->meta['created_by_manual_renewal']);
    }

    public function getStatusBadgeAttribute(): array
    {
        return app(InvoicePresenter::class)->statusBadge($this);
    }

    public function getBarColorAttribute(): string
    {
        return app(InvoicePresenter::class)->barColor($this);
    }

    public function getIconBoxClassAttribute(): string
    {
        return app(InvoicePresenter::class)->iconBoxClass($this);
    }

    public function getJalaliIssueDateAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->issue_date);
    }

    public function getJalaliDueDateAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->due_date);
    }

    public function getJalaliPaidAtAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->paid_at);
    }

    public function getSubAccountPhoneAttribute(): ?string
    {
        return $this->sub_account_mobile;
    }

    public function getTotalAmountAttribute(): int
    {
        return (int)($this->attributes['total'] ?? 0);
    }

    public function getCurrencyAttribute(): string
    {
        return (string)($this->attributes['currency'] ?? CurrencyContext::forReseller((int)$this->reseller_id));
    }

    public function money(string $attribute = 'total'): Money
    {
        return Money::of($this->getAttribute($attribute), $this->currency);
    }

    public static function generateNextNumber(int $resellerId, string $type = self::TYPE_OFFICIAL): string
    {
        return app(InvoiceNumberGenerator::class)->generate($resellerId, $type);
    }

    public function recalculateTotals(): self
    {
        return app(InvoiceTotalsCalculator::class)->recalculate($this);
    }
}
