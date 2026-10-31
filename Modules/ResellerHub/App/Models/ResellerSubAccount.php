<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Enums\AccountStatus;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Services\TenantQuotaService;
use Throwable;

/**
 * @property int $id
 * @property int $reseller_id
 * @property int|null $tenant_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $company_name
 * @property string $status
 * @property int|null $max_invoices
 * @property int|null $max_tenants
 * @property array|null $settings
 * @property array|null $limits
 * @property-read User|null $user
 * @property-read Tenant|null $tenant
 * @method static ResellerSubAccount|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerSubAccount findOrFail(mixed $id, array $columns = ['*'])
 * @method static Builder|ResellerSubAccount where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ResellerSubAccount extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = AccountStatus::ACTIVE->value;
    public const STATUS_SUSPENDED = AccountStatus::SUSPENDED->value;

    protected $table = 'reseller_sub_accounts';

    protected $fillable = [
        'reseller_id',
        'tenant_id',
        'user_id',
        'name',
        'email',
        'mobile',
        'password',
        'company_name',
        'status',
        'settings',
        'limits',
        'max_invoices',
        'max_tenants',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'settings' => 'array',
        'limits' => 'array',
        'max_invoices' => 'integer',
        'max_tenants' => 'integer',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'sub_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            ResellerInvoice::class,
            Tenant::class,
            'id',        // Local key on tenants (tenants.id)
            'tenant_id', // Foreign key on reseller_invoices (reseller_invoices.tenant_id)
            'tenant_id', // Foreign key on reseller_sub_accounts (reseller_sub_accounts.tenant_id)
            'id'         // Local key on tenants (tenants.id)
        );
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            ResellerOrder::class,
            Tenant::class,
            'id',        // Local key on tenants (tenants.id)
            'tenant_id', // Foreign key on reseller_orders (reseller_orders.tenant_id)
            'tenant_id', // Foreign key on reseller_sub_accounts (reseller_sub_accounts.tenant_id)
            'id'         // Local key on tenants (tenants.id)
        );
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%");
        });
    }

    public function getJalaliCreatedAtAttribute(): string
    {
        return JalaliDateHelper::formatDate($this->created_at, 'Y/m/d H:i') ?? '';
    }

    public function getTimeAgoAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        $diffInSeconds = now()->diffInSeconds($this->created_at);

        if ($diffInSeconds < 60) {
            return 'هم‌اکنون';
        }
        if ($diffInSeconds < 3600) {
            $minutes = round($diffInSeconds / 60);
            return "{$minutes} دقیقه پیش";
        }
        if ($diffInSeconds < 86400) {
            $hours = round($diffInSeconds / 3600);
            return "{$hours} ساعت پیش";
        }
        $days = round($diffInSeconds / 86400);
        return "{$days} روز پیش";
    }

    public function getStatusEnumAttribute(): ?AccountStatus
    {
        return $this->status instanceof AccountStatus
            ? $this->status
            : AccountStatus::fromOrDefault((string)$this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum?->label() ?? 'نامشخص';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status_enum?->badgeClasses() ?? 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }

    public function isOnlinePurchase(): bool
    {
        $settings = is_array($this->settings) ? $this->settings : [];
        $src = $settings['source'] ?? null;
        return ResellerHelper::isOnlineSource($src);
    }

    public function getSourceLabelAttribute(): string
    {
        if ($this->isOnlinePurchase()) {
            $settings = is_array($this->settings) ? $this->settings : [];
            return $settings['source_label'] ?? 'خرید آنلاین از سایت';
        }
        return 'ثبت دستی نماینده';
    }

    public function getSourceBadgeAttribute(): string
    {
        if ($this->isOnlinePurchase()) {
            return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80';
        }
        return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200/60 dark:border-gray-700';
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('settings->source', ResellerHelper::ONLINE_SOURCES)
                ->orWhere('settings->source', 'like', 'portal_%')
                ->orWhere('settings->source', 'like', 'online_%')
                ->orWhere('settings->source', 'like', 'dashboard_%')
                ->orWhere('settings->source', 'like', 'website_%');
        });
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $sq) {
                $sq->whereNull('settings->source')
                    ->orWhereNotIn('settings->source', ResellerHelper::ONLINE_SOURCES);
            })
            ->where(function (Builder $sq) {
                $sq->whereNull('settings->source')
                    ->orWhere(function ($ssq) {
                        $ssq->where('settings->source', 'not like', 'portal_%')
                            ->where('settings->source', 'not like', 'online_%')
                            ->where('settings->source', 'not like', 'dashboard_%')
                            ->where('settings->source', 'not like', 'website_%');
                    });
            });
        });
    }

    /**
     * Get limit for any module or capability.
     * Checks limits JSON first, then legacy columns, then package limits if attached.
     */
    public function getLimit(string|QuotaKey $key, $default = null): ?int
    {
        $keyName = $key instanceof QuotaKey ? $key->value : (string)$key;

        if (is_array($this->limits) && array_key_exists($keyName, $this->limits)) {
            $val = $this->limits[$keyName];
            return ($val === '' || $val === null) ? null : (int)$val;
        }

        if ($keyName === QuotaKey::Invoices->value && !is_null($this->max_invoices)) {
            return (int)$this->max_invoices;
        }

        // Fallback to active tenant package limit if available
        if ($this->tenant && $this->tenant->package) {
            return $this->tenant->package->getLimit($keyName, $default);
        }

        return $default;
    }

    /**
     * Check if a specific limit key is defined.
     */
    public function hasLimit(string|QuotaKey $key): bool
    {
        return !is_null($this->getLimit($key));
    }

    /**
     * Returns true if unlimited (max_invoices is null) or under limit.
     */
    public function hasInvoiceQuota(): bool
    {
        if (!is_null($this->max_invoices)) {
            $max = (int)$this->max_invoices;
            if ($max <= 0) {
                return true; // unlimited
            }
            return $this->invoices()->count() < $max;
        }

        if ($this->tenant && $this->tenant->hasActivePaidPlan()) {
            return app(TenantQuotaService::class)->hasRemainingQuota($this->tenant, QuotaKey::Invoices);
        }

        $limit = $this->getLimit(QuotaKey::Invoices);
        if (is_null($limit) || $limit <= 0) {
            return true; // unlimited
        }
        return $this->invoices()->count() < $limit;
    }

    /**
     * Return remaining invoice quota, or null if unlimited.
     */
    public function remainingInvoiceQuota(): ?int
    {
        if ($this->tenant) {
            return app(TenantQuotaService::class)->remainingQuota($this->tenant, QuotaKey::Invoices);
        }

        $limit = $this->getLimit(QuotaKey::Invoices);
        if (is_null($limit)) {
            return null; // unlimited
        }
        return max(0, $limit - $this->invoices()->count());
    }
}

