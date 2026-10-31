<?php
namespace Modules\Clients\Entities;

use Illuminate\Database\Eloquent\Model;

class ClientStatus extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'label',
        'color',
        'is_system',
        'is_active',
        'show_in_quick',
        'sort_order',
        'allowed_from',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant_or_system', function ($builder) {
            $tm = app(\App\Tenancy\TenantManager::class);
            if ($tm->isBypassed()) {
                return;
            }
            if ($tm->hasTenant()) {
                $tenantId = $tm->getTenantId();
                $builder->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)
                      ->orWhere('is_system', true)
                      ->orWhereNull('tenant_id');
                });
            } else {
                $builder->where(function ($q) {
                    $q->whereNull('tenant_id')
                      ->orWhere('is_system', true);
                });
            }
        });

        static::creating(function ($model) {
            $tm = app(\App\Tenancy\TenantManager::class);
            if (empty($model->tenant_id) && $tm->hasTenant()) {
                $model->tenant_id = $tm->getTenantId();
            }
        });
    }

    protected $casts = [
        'is_system'   => 'bool',
        'is_active'   => 'bool',
        'show_in_quick' => 'bool',
        'allowed_from'  => 'array',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'status_id');
    }

    // فقط وضعیت‌های فعال
    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
