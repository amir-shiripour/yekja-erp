<?php

namespace Modules\ResellerHub\App\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reseller_id
 * @property string $module_slug
 * @property string|null $feature_key
 * @property string|null $feature_title
 * @property int $monthly_price
 * @property int $quarterly_price
 * @property int $semi_annual_price
 * @property int $yearly_price
 * @property int $setup_fee
 * @property bool $is_active
 * @property string|null $description
 * @property-read Reseller $reseller
 * @method static Builder|ResellerModulePricing where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ResellerModulePricing extends Model
{
    protected $table = 'reseller_module_pricings';

    protected $fillable = [
        'reseller_id',
        'module_slug',
        'feature_key',
        'feature_title',
        'monthly_price',
        'quarterly_price',
        'semi_annual_price',
        'yearly_price',
        'setup_fee',
        'is_active',
        'description',
    ];

    protected $casts = [
        'monthly_price' => 'integer',
        'quarterly_price' => 'integer',
        'semi_annual_price' => 'integer',
        'yearly_price' => 'integer',
        'setup_fee' => 'integer',
        'is_active' => 'boolean',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForReseller($query, $resellerId)
    {
        return $query->where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        });
    }

    public function isBaseModule(): bool
    {
        return empty($this->feature_key);
    }

    public function isFeature(): bool
    {
        return !empty($this->feature_key);
    }

    public static function allKeyedBySlug(int $resellerId): array
    {
        return static::where('reseller_id', $resellerId)
            ->where('is_active', true)
            ->get()
            ->keyBy(function ($item) {
                return $item->feature_key ? ($item->module_slug . ':' . $item->feature_key) : $item->module_slug;
            })
            ->toArray();
    }
}
