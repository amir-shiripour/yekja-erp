<?php

namespace Modules\Clients\Entities;

use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;

class ClientSetting extends Model
{
    protected $table = 'client_settings';
    protected $fillable = ['tenant_id', 'key', 'value'];

    protected static function getCacheKey(string $key): string
    {
        $tenantId = app(TenantManager::class)->getTenantId() ?? 'direct';
        return "clients.settings.{$tenantId}.{$key}";
    }

    public static function getValue(string $key, $default = null)
    {
        $cacheKey = static::getCacheKey($key);
        return cache()->remember($cacheKey, 3600, function () use ($key, $default) {
            return optional(static::query()->where('key', $key)->first())->value ?? $default;
        });
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        cache()->forget(static::getCacheKey($key));
    }
}
