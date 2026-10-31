<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ResellerHub\App\Enums\AccountStatus;

class Reseller extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = AccountStatus::ACTIVE->value;
    public const STATUS_SUSPENDED = AccountStatus::SUSPENDED->value;

    protected $table = 'resellers';

    protected static function booted(): void
    {
        static::deleting(function (Reseller $reseller) {
            if ($reseller->invoices()->exists() || $reseller->orders()->exists()) {
                throw new \DomainException('امکان حذف فیزیکی نماینده دارای تراکنش‌های مالی یا سفارش وجود ندارد. فقط تعلیق مجاز است.');
            }
        });
    }

    public function suspend(): bool
    {
        return $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    protected $fillable = [
        'user_id',
        'company_name',
        'brand_title',
        'brand_logo',
        'brand_favicon',
        'brand_footer_text',
        'support_phone',
        'support_email',
        'status',
    ];

    protected $casts = [
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'reseller_id');
    }


    public function allowedModules(): HasMany
    {
        return $this->hasMany(ResellerAllowedModule::class, 'reseller_id');
    }


    public function settings(): HasMany
    {
        return $this->hasMany(ResellerSetting::class, 'reseller_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ResellerPackage::class, 'reseller_id');
    }

    public function modulePricings(): HasMany
    {
        return $this->hasMany(ResellerModulePricing::class, 'reseller_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ResellerInvoice::class, 'reseller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ResellerOrder::class, 'reseller_id');
    }


    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }


    public function activeTenantsCount(): int
    {
        return $this->tenants()->where('status', Tenant::STATUS_ACTIVE)->count();
    }


    /**
     *
     * @var array<int, string>|null
     */
    protected ?array $allowedModuleSlugsCache = null;

    public function canSellModule(string $slug): bool
    {
        $allowed = $this->allowedModuleSlugs();

        // No explicit allow-list means "everything is sellable".
        if ($allowed === []) {
            return true;
        }

        return in_array(strtolower(trim($slug)), $allowed, true);
    }


    public function allowedModuleSlugs(): array
    {
        if ($this->allowedModuleSlugsCache !== null) {
            return $this->allowedModuleSlugsCache;
        }

        return $this->allowedModuleSlugsCache = $this->allowedModules()
            ->pluck('module_slug')
            ->map(fn($s) => strtolower((string)$s))
            ->all();
    }

    /**
     * Drop the memo — call after mutating the allow-list.
     */
    public function forgetAllowedModulesCache(): void
    {
        $this->allowedModuleSlugsCache = null;
    }

    public function subAccounts(): HasMany
    {
        return $this->hasMany(ResellerSubAccount::class, 'reseller_id');
    }

    public function allowsCustomDomains(): bool
    {
        return (bool)ResellerSetting::get($this->id, 'allow_custom_domains', true);
    }

    public function getCurrency(): string
    {
        return ResellerSetting::getCurrencyForReseller($this->id);
    }

    public function getCurrencyLabel(): string
    {
        return ResellerSetting::getCurrencyLabelForReseller($this->id);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: ($this->brand_title ?: ($this->user?->name ?? '—'));
    }

    public function getSupportContactAttribute(): string
    {
        return $this->support_phone ?: ($this->support_email ?? '—');
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
}
