<?php

namespace App\Models;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Morilog\Jalali\Jalalian;
use Throwable;

/**
 * @property int $id
 * @property int|null $reseller_id
 * @property int|null $package_id
 * @property int|null $sub_account_id
 * @property string $name
 * @property string $subdomain
 * @property string|null $custom_domain
 * @property string $status
 * @property Carbon|null $plan_expires_at
 * @property Carbon|null $checkout_expires_at
 * @property array|null $branding
 * @property array|null $settings
 * @property-read ResellerSubAccount|null $subAccount
 * @method static Tenant|null find(mixed $id, array $columns = ['*'])
 * @method static Tenant findOrFail(mixed $id, array $columns = ['*'])
 * @method static Builder|Tenant where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    protected $table = 'tenants';

    protected $fillable = [
        'reseller_id',
        'package_id',
        'sub_account_id',
        'name',
        'subdomain',
        'custom_domain',
        'status',
        'plan_expires_at',
        'checkout_expires_at',
        'branding',
        'settings',
    ];

    protected $casts = [
        'plan_expires_at' => 'datetime',
        'checkout_expires_at' => 'datetime',
        'branding' => 'array',
        'settings' => 'array',
    ];


    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ResellerPackage::class, 'package_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }


    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'tenant_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(TenantModule::class, 'tenant_id');
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(ResellerSubAccount::class, 'sub_account_id');
    }

    /**
     * The tenant's primary administrator: the user behind its sub-account.
     *
     * Resolved through `reseller_sub_accounts.tenant_id` rather than
     * `tenants.sub_account_id` so it also works for tenants whose back-link was
     * never written. Null when the tenant has no sub-account yet.
     */
    public function owner(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            ResellerSubAccount::class,
            'tenant_id',  // foreign key on reseller_sub_accounts
            'id',         // foreign key on users
            'id',         // local key on tenants
            'user_id'     // local key on reseller_sub_accounts
        );
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ResellerInvoice::class, 'tenant_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ResellerOrder::class, 'tenant_id');
    }


    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isExpired(): bool
    {
        // Null expiration date means permanent / unlimited access
        if (is_null($this->plan_expires_at)) {
            return false;
        }

        // One-time and Free packages never expire
        if ($this->package && !$this->package->getPriceTypeEnum()->hasRenewal()) {
            return false;
        }

        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->plan_expires_at && Carbon::now()->greaterThan($this->plan_expires_at)) {
            return true;
        }

        return false;
    }

    public function isRenewable(): bool
    {
        if ($this->package && !$this->package->getPriceTypeEnum()->hasRenewal()) {
            return false;
        }

        return true;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function hasCheckoutExpired(): bool
    {
        return $this->isPendingPayment()
            && $this->checkout_expires_at !== null
            && $this->checkout_expires_at->isPast();
    }

    public function scopePendingCheckout($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    public function isAccessible(): bool
    {
        return $this->isActive();
    }


    public function hasActiveOrder(): bool
    {
        if (!$this->orders()->exists()) {
            return true;
        }

        return $this->orders()
            ->whereIn('status', [ResellerOrder::STATUS_ACTIVE, 'completed'])
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->exists();
    }

    public function hasActivePaidPlan(): bool
    {
        if ($this->package && $this->package->isFree()) {
            return true;
        }

        return $this->orders()
            ->whereIn('status', [ResellerOrder::STATUS_ACTIVE, 'completed'])
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->exists();
    }

    public function isModulePurchased(string $moduleSlug): bool
    {
        $normalized = strtolower(trim($moduleSlug));

        if ($this->modules()->whereRaw('LOWER(module_slug) = ?', [$normalized])->exists()) {
            return true;
        }

        if ($this->package && is_array($this->package->modules)) {
            $pkgModules = array_map('strtolower', array_map('trim', $this->package->modules));
            if (in_array($normalized, $pkgModules, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasModule(string $moduleSlug): bool
    {
        $normalized = strtolower(trim($moduleSlug));

        if ($this->isSuspended() || $this->isExpired() || $this->isPendingPayment()) {
            return false;
        }

        if (!$this->hasActiveOrder()) {
            return false;
        }

        return $this->modules()
            ->where('is_active', true)
            ->whereRaw('LOWER(module_slug) = ?', [$normalized])
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->exists();
    }

    public function activeModuleSlugs(): array
    {
        if ($this->isSuspended() || $this->isExpired() || $this->isPendingPayment()) {
            return [];
        }

        if (!$this->hasActiveOrder()) {
            return [];
        }

        return $this->modules()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->pluck('module_slug')
            ->map(fn($s) => strtolower($s))
            ->toArray();
    }

    public function getBranding(string $key, $default = null)
    {
        $val = data_get($this->branding, $key);
        if (!is_null($val) && $val !== '') {
            return $val;
        }

        // Fallback to reseller branding if available
        if ($this->reseller) {
            if ($key === 'brand_primary_color') {
                $resellerSettings = \Modules\ResellerHub\App\Models\ResellerSetting::allForReseller($this->reseller_id);
                $rColor = $resellerSettings['brand_primary_color'] ?? null;
                if (!empty($rColor)) {
                    return $rColor;
                }
            } else {
                $rVal = $this->reseller->{$key} ?? null;
                if (!is_null($rVal) && $rVal !== '') {
                    return $rVal;
                }
            }
        }

        return $default;
    }

    /**
     * Get configured limit for a module or specific capability.
     * Checks tenant settings first, then falls back to package limit.
     * Null means unlimited.
     */
    public function getLimit(string $key, $default = null): ?int
    {
        $limits = data_get($this->settings, 'limits');
        if (is_array($limits) && array_key_exists($key, $limits)) {
            $val = $limits[$key];
            return ($val === '' || $val === null) ? null : (int)$val;
        }

        if ($this->package) {
            return $this->package->getLimit($key, $default);
        }

        return $default;
    }

    /**
     * Check if a specific limit key is defined.
     */
    public function hasLimit(string $key): bool
    {
        return !is_null($this->getLimit($key));
    }

    /**
     * Get Jalali created_at string.
     */
    public function getJalaliCreatedAtAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        try {
            return Jalalian::fromCarbon($this->created_at)->format('Y/m/d');
        } catch (Throwable $e) {
            return $this->created_at->format('Y/m/d');
        }
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isPendingPayment()) {
            return 'در انتظار پرداخت';
        }
        if ($this->isSuspended()) {
            return 'مسدود / تعلیق';
        }
        if ($this->isExpired()) {
            return 'منقضی شده';
        }
        return 'فعال';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isPendingPayment()) {
            return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/50 dark:border-blue-800/40';
        }
        if ($this->isSuspended()) {
            return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/50 dark:border-amber-800/40';
        }
        if ($this->isExpired()) {
            return 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border-red-200/50 dark:border-red-800/40';
        }
        return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/50 dark:border-emerald-800/40';
    }

    public function getStatusDotClassAttribute(): string
    {
        if ($this->isPendingPayment()) {
            return 'bg-blue-500';
        }
        if ($this->isSuspended()) {
            return 'bg-amber-500';
        }
        if ($this->isExpired()) {
            return 'bg-red-500';
        }
        return 'bg-emerald-500';
    }

    public function getJalaliPlanExpiresAtAttribute(): ?string
    {
        return $this->plan_expires_at ? reseller_format_jalali($this->plan_expires_at) : null;
    }

    /**
     * Check if tenant has paid for a specific addon type in any paid invoice.
     */
    public function hasPaidAddon(string $itemType): bool
    {
        return $this->invoices()
            ->where('status', \Modules\ResellerHub\App\Enums\InvoiceStatus::PAID->value)
            ->whereHas('items', function ($q) use ($itemType) {
                $q->where('item_type', $itemType)
                    ->orWhere('item_key', $itemType);
            })
            ->exists();
    }

    /**
     * Check whether this tenant is entitled to configure a custom domain.
     */
    public function allowsCustomDomain(): bool
    {
        // 1. Reseller global toggle check
        if ($this->reseller && method_exists($this->reseller, 'allowsCustomDomains') && !$this->reseller->allowsCustomDomains()) {
            return false;
        }

        // 2. If tenant already has a custom_domain attached
        if (!empty($this->custom_domain)) {
            return true;
        }

        // 3. Check if active package has custom_domain feature
        if ($this->package && method_exists($this->package, 'hasFeature') && $this->package->hasFeature('custom_domain')) {
            return true;
        }

        // 4. Check explicit tenant settings features
        $features = data_get($this->settings, 'features', []);
        if (is_array($features)) {
            if (in_array('custom_domain', $features, true) || !empty($features['custom_domain'])) {
                return true;
            }
        }

        // 5. Check if purchased as an addon
        if ($this->hasPaidAddon('custom_domain')) {
            return true;
        }

        // 6. Check if reseller offers custom domain for free
        if ($this->reseller_id) {
            $yearly = (int)\Modules\ResellerHub\App\Models\ResellerSetting::get($this->reseller_id, 'custom_domain_yearly_price', 0);
            $monthly = (int)\Modules\ResellerHub\App\Models\ResellerSetting::get($this->reseller_id, 'custom_domain_monthly_price', 0);
            if ($yearly === 0 && $monthly === 0) {
                return true;
            }
        }

        // Central direct tenant fallback (no reseller)
        if (!$this->reseller_id) {
            return true;
        }

        return false;
    }

    /**
     * Check whether this tenant is entitled to configure white-label branding.
     */
    public function allowsWhiteLabel(): bool
    {
        // 1. If tenant already has custom branding active
        if (!empty(data_get($this->branding, 'is_custom'))) {
            return true;
        }

        // 2. Check if active package has white_label feature
        if ($this->package && method_exists($this->package, 'hasFeature') && $this->package->hasFeature('white_label')) {
            return true;
        }

        // 3. Check explicit tenant settings features
        $features = data_get($this->settings, 'features', []);
        if (is_array($features)) {
            if (in_array('white_label', $features, true) || !empty($features['white_label'])) {
                return true;
            }
        }

        // 4. Check if purchased as an addon
        if ($this->hasPaidAddon('white_label')) {
            return true;
        }

        // 5. Check if reseller offers white-label for free
        if ($this->reseller_id) {
            $yearly = (int)\Modules\ResellerHub\App\Models\ResellerSetting::get($this->reseller_id, 'white_label_yearly_price', 0);
            $monthly = (int)\Modules\ResellerHub\App\Models\ResellerSetting::get($this->reseller_id, 'white_label_monthly_price', 0);
            if ($yearly === 0 && $monthly === 0) {
                return true;
            }
        }

        // Central direct tenant fallback (no reseller)
        if (!$this->reseller_id) {
            return true;
        }

        return false;
    }
}
