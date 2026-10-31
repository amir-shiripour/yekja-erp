<?php

namespace Modules\DirectAdmin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class DaServer extends Model
{
    protected $table = 'da_servers';

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'ssl',
        'is_active',
        'last_connected_at',
        'last_error',
        'metadata',
    ];

    protected $casts = [
        'ssl' => 'boolean',
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Encrypt password when saving
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Crypt::encryptString($value);
        } else {
            $this->attributes['password'] = null;
        }
    }

    /**
     * Decrypt password when retrieving
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        if (!empty($this->attributes['password'])) {
            try {
                return Crypt::decryptString($this->attributes['password']);
            } catch (Throwable $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Check if this server is a manual/offline server
     */
    public function isManual(): bool
    {
        return ($this->username === 'manual')
            || empty($this->password)
            || (isset($this->metadata['type']) && $this->metadata['type'] === 'manual');
    }

    /**
     * Check if this server is currently set as default
     */
    public function isDefault(): bool
    {
        return (string)DaSetting::get('default_server_id') === (string)$this->id;
    }

    public function accounts()
    {
        return $this->hasMany(DaAccount::class, 'da_server_id');
    }
}
