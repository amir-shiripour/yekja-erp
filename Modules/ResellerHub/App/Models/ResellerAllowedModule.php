<?php

namespace Modules\ResellerHub\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reseller_id
 * @property string $module_slug
 * @property-read Reseller $reseller
 * @method static Builder|ResellerAllowedModule query()
 * @method static ResellerAllowedModule|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerAllowedModule create(array $attributes = [])
 * @method static Builder|ResellerAllowedModule where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ResellerAllowedModule extends Model
{
    protected $table = 'reseller_allowed_modules';

    protected $fillable = [
        'reseller_id',
        'module_slug',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }
}
