<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\Tenant;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Enums\PackagePriceType;
use Modules\ResellerHub\App\Enums\PackageDiscountType;
use Modules\ResellerHub\App\Enums\PackageColor;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Presentation\PackagePresenter;
use Modules\ResellerHub\App\Services\Pricing\PriceResolver;

/**
 * @property int $id
 * @property int $reseller_id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property array|null $modules
 * @property array|null $features
 * @property array|null $limits
 * @property string $price_type
 * @property array|null $active_periods
 * @property array|null $period_prices
 * @property int|null $monthly_price
 * @property int|null $quarterly_price
 * @property int|null $semi_annual_price
 * @property int|null $yearly_price
 * @property int|null $one_time_price
 * @property int|null $setup_fee
 * @property string|null $discount_type
 * @property float|null $discount_value
 * @property Carbon|null $discount_starts_at
 * @property Carbon|null $discount_expires_at
 * @property bool $is_active
 * @property int $sort_order
 * @property-read bool $is_free
 * @property-read string|null $discount_starts_at_jalali
 * @property-read string|null $discount_expires_at_jalali
 * @method static Builder|ResellerPackage query()
 * @method static ResellerPackage|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerPackage findOrFail(mixed $id, array $columns = ['*'])
 * @method static ResellerPackage create(array $attributes = [])
 * @method static Builder|ResellerPackage where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder|ResellerPackage whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static Builder|ResellerPackage orderBy(string $column, string $direction = 'asc')
 */
class ResellerPackage extends Model
{
    protected $table = 'reseller_packages';

    public const PRICE_TYPE_SUBSCRIPTION = PackagePriceType::SUBSCRIPTION->value;
    public const PRICE_TYPE_ONE_TIME = PackagePriceType::ONE_TIME->value;
    public const PRICE_TYPE_FREE = PackagePriceType::FREE->value;

    public const DISCOUNT_TYPE_AMOUNT = PackageDiscountType::AMOUNT->value;

    public const PERIOD_MONTHLY = 'monthly_price';
    public const PERIOD_QUARTERLY = 'quarterly_price';
    public const PERIOD_SEMI_ANNUAL = 'semi_annual_price';
    public const PERIOD_YEARLY = 'yearly_price';

    public const PERIOD_KEYS = [
        self::PERIOD_MONTHLY,
        self::PERIOD_QUARTERLY,
        self::PERIOD_SEMI_ANNUAL,
        self::PERIOD_YEARLY,
    ];

    protected $fillable = [
        'reseller_id',
        'name',
        'description',
        'icon',
        'color',
        'modules',
        'features',
        'limits',
        'price_type',
        'active_periods',
        'period_prices',
        'period_discounts',
        'monthly_price',
        'quarterly_price',
        'semi_annual_price',
        'yearly_price',
        'one_time_price',
        'setup_fee',
        'discount_type',
        'discount_value',
        'discount_starts_at',
        'discount_expires_at',
        'is_active',
        'sort_order',
    ];

    protected $appends = [
        'is_free',
        'discount_starts_at_jalali',
        'discount_expires_at_jalali',
    ];

    protected $casts = [
        'modules' => 'array',
        'features' => 'array',
        'limits' => 'array',
        'active_periods' => 'array',
        'period_prices' => 'array',
        'period_discounts' => 'array',
        'monthly_price' => 'integer',
        'quarterly_price' => 'integer',
        'semi_annual_price' => 'integer',
        'yearly_price' => 'integer',
        'one_time_price' => 'integer',
        'setup_fee' => 'integer',
        'discount_value' => 'float',
        'discount_starts_at' => 'datetime',
        'discount_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'package_id');
    }

    public function scopeForReseller($query, $resellerId)
    {
        return $query->where('reseller_id', $resellerId)
            ->orderBy('sort_order')
            ->latest('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function isActive(): bool
    {
        return (bool)($this->is_active ?? true);
    }

    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    public function getModulesCountAttribute(): int
    {
        return is_array($this->modules) ? count($this->modules) : 0;
    }

    public function getFeaturesCountAttribute(): int
    {
        return is_array($this->features) ? count($this->features) : 0;
    }

    public function hasFeature(string|QuotaKey $featureKey): bool
    {
        if (!is_array($this->features)) {
            return false;
        }

        $key = $featureKey instanceof QuotaKey ? $featureKey->value : (string)$featureKey;

        return in_array($key, $this->features, true);
    }

    public function isFree(): bool
    {
        if ($this->price_type === self::PRICE_TYPE_FREE) {
            return true;
        }

        if ($this->isOneTime()) {
            return (int)$this->one_time_price <= 0;
        }

        if ($this->isSubscription()) {
            if (is_array($this->period_prices) && !empty($this->period_prices)) {
                foreach ($this->period_prices as $price) {
                    if ((int)$price > 0) {
                        return false;
                    }
                }
            }

            if ((int)$this->monthly_price > 0 || (int)$this->quarterly_price > 0 || (int)$this->semi_annual_price > 0 || (int)$this->yearly_price > 0) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getIsFreeAttribute(): bool
    {
        return $this->isFree();
    }

    public function getPriceTypeEnum(): PackagePriceType
    {
        return PackagePriceType::fromOrDefault($this->price_type);
    }

    public function getDiscountTypeEnum(): PackageDiscountType
    {
        return PackageDiscountType::fromOrDefault($this->discount_type);
    }

    public function getColorEnum(): PackageColor
    {
        return PackageColor::fromOrDefault($this->color);
    }

    public function getPriceTypeLabelAttribute(): string
    {
        return app(PackagePresenter::class)->priceTypeLabel($this);
    }

    public function hasActiveDiscount(): bool
    {
        return $this->pricing()->hasGlobalActiveDiscount($this);
    }

    public function isDiscountExpired(): bool
    {
        return $this->discount_expires_at && $this->discount_expires_at->isPast();
    }

    public function isDiscountNotYetStarted(): bool
    {
        return $this->discount_starts_at && $this->discount_starts_at->isFuture();
    }

    public function getDiscountStartsAtJalaliAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->discount_starts_at);
    }

    public function getDiscountExpiresAtJalaliAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->discount_expires_at);
    }

    public function isOneTime(): bool
    {
        return $this->price_type === self::PRICE_TYPE_ONE_TIME;
    }

    public function isSubscription(): bool
    {
        return $this->price_type === self::PRICE_TYPE_SUBSCRIPTION || empty($this->price_type);
    }

    public static function resolveMonthFromPeriodKey(string|int $key): int
    {
        return BillingCycle::resolveMonthFromPeriodKey($key);
    }

    public static function resolvePeriodKeyFromMonth(int $months): string
    {
        return BillingCycle::resolvePeriodKeyFromMonth($months);
    }

    public function getActivePeriods(): array
    {
        return $this->pricing()->activePeriods($this);
    }

    public function getActiveMonths(): array
    {
        return $this->pricing()->activeMonths($this);
    }

    public function isPeriodActive(string|int $key): bool
    {
        return $this->pricing()->isPeriodActive($this, $key);
    }

    /**
     * Get complete structured period definitions for this package with calculated prices and discounts.
     *
     * @return array<string, array{key: string, months: int, label: string, raw_price: int, final_price: int, discount_amount: int, has_discount: bool, discount_percent: int, monthly_rate: int}>
     */
    public function getPackagePeriods(): array
    {
        return $this->pricing()->periods($this);
    }

    public function getPeriodDiscounts(): array
    {
        return is_array($this->period_discounts) ? $this->period_discounts : [];
    }

    public function getDiscountConfigForMonths(int $months): ?array
    {
        return $this->pricing()->discountConfig($this, $months);
    }

    public function hasCustomDiscountForMonths(int $months): bool
    {
        return $this->pricing()->hasCustomDiscount($this, $months);
    }

    public function hasActiveDiscountForMonths(int $months): bool
    {
        return $this->pricing()->hasActiveDiscount($this, $months);
    }

    public function getDiscountStartsAtJalaliForMonths(int $months): ?string
    {
        if ($this->hasCustomDiscountForMonths($months)) {
            $cfg = $this->getDiscountConfigForMonths($months);
            $startsRaw = $cfg['discount_starts_at'] ?? $cfg['starts_at'] ?? null;
            return JalaliDateHelper::formatDate($startsRaw);
        }

        return $this->discount_starts_at_jalali;
    }

    public function getDiscountExpiresAtJalaliForMonths(int $months): ?string
    {
        if ($this->hasCustomDiscountForMonths($months)) {
            $cfg = $this->getDiscountConfigForMonths($months);
            $expiresRaw = $cfg['discount_expires_at'] ?? $cfg['expires_at'] ?? null;
            return JalaliDateHelper::formatDate($expiresRaw);
        }

        return $this->discount_expires_at_jalali;
    }

    public function getDiscountAmountForMonths(int $months, int $rawPrice): int
    {
        return $this->pricing()->discountAmount($this, $months, $rawPrice);
    }

    public function getDiscountedPrice(int $rawPrice): int
    {
        return $this->pricing()->discountedPrice($this, $rawPrice);
    }

    public function getFinalMonthlyPriceAttribute(): int
    {
        return $this->getFinalPriceForMonths(1);
    }

    public function getFinalQuarterlyPriceAttribute(): int
    {
        return $this->getFinalPriceForMonths(3);
    }

    public function getFinalSemiAnnualPriceAttribute(): int
    {
        return $this->getFinalPriceForMonths(6);
    }

    public function getFinalYearlyPriceAttribute(): int
    {
        return $this->getFinalPriceForMonths(12);
    }

    public function getFinalOneTimePriceAttribute(): int
    {
        return $this->getDiscountedPrice((int)$this->one_time_price);
    }

    public function getFinalSetupFeeAttribute(): int
    {
        return max(0, (int)$this->setup_fee);
    }


    public function getPriceForMonths(int $months): int
    {
        return $this->pricing()->rawPrice($this, $months);
    }

    public function getFinalPriceForMonths(int $months): int
    {
        return $this->pricing()->finalPrice($this, $months);
    }

    public function isUpgradeOver(?self $current): bool
    {
        return $this->pricing()->isUpgradeOver($this, $current);
    }

    public function getDiscountedPriceForMonths(int $months): int
    {
        return $this->getFinalPriceForMonths($months);
    }

    public function calculatePriceForDuration(int $months): int
    {
        return $this->getFinalPriceForMonths($months);
    }

    /**
     * Get dynamic color hex directly from database or mapped fallback.
     */
    public function getColorHexAttribute(): string
    {
        return app(PackagePresenter::class)->colorHex($this);
    }

    /**
     * Get inline style for icon background.
     */
    public function getBgStyleAttribute(): string
    {
        return app(PackagePresenter::class)->backgroundStyle($this);
    }

    /**
     * Get inline style for light pill or card background.
     */
    public function getLightBgStyleAttribute(): string
    {
        return app(PackagePresenter::class)->lightBackgroundStyle($this);
    }

    /**
     * Get configured limit for a module or specific capability.
     * Null or empty means unlimited.
     */
    public function getLimit(string|QuotaKey $key, $default = null): ?int
    {
        $keyName = $key instanceof QuotaKey ? $key->value : (string)$key;

        if (!is_array($this->limits) || !array_key_exists($keyName, $this->limits)) {
            return $default;
        }

        $val = $this->limits[$keyName];
        return ($val === '' || $val === null) ? null : (int)$val;
    }

    /**
     * Check if a specific limit key is defined.
     */
    public function hasLimit(string|QuotaKey $key): bool
    {
        $keyName = $key instanceof QuotaKey ? $key->value : (string)$key;

        return is_array($this->limits) && array_key_exists($keyName, $this->limits) && $this->limits[$keyName] !== '' && $this->limits[$keyName] !== null;
    }

    private function pricing(): PriceResolver
    {
        return app(PriceResolver::class);
    }
}

