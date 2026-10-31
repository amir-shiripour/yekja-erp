<?php

namespace Modules\DomainManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class DomainReseller extends Model
{
    protected $table = 'domain_resellers';

    protected $fillable = [
        'name',
        'auth_userid',
        'api_key',
        'is_active',
        'balance',
        'locked_balance',
        'currency',
        'last_connected_at',
        'last_error'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:4',
        'locked_balance' => 'decimal:4',
        'last_connected_at' => 'datetime'
    ];

    /**
     * Mutator to encrypt api_key automatically
     */
    public function setApiKeyAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        } else {
            $this->attributes['api_key'] = null;
        }
    }

    /**
     * Accessor to decrypt api_key automatically
     */
    public function getDecryptedApiKeyAttribute()
    {
        if (empty($this->attributes['api_key'])) {
            return '';
        }
        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * Check if this reseller is a manual/offline provider
     */
    public function isManual(): bool
    {
        return ($this->auth_userid === 'manual') || empty($this->api_key);
    }

    /**
     * Relationship to domains
     */
    public function domains()
    {
        return $this->hasMany(DomainRecord::class, 'domain_reseller_id');
    }
}
