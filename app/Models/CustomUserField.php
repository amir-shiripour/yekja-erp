<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomUserField extends Model
{
    use HasFactory;

    public const ENTITY_USER = 'user';
    public const ENTITY_SUB_ACCOUNT = 'sub_account';

    protected $fillable = [
        'entity_type',
        'role_name',
        'field_name',
        'label',
        'field_type',
        'is_required',
        'rules',
    ];

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_name', 'name');
    }

    public function scopeForUsers($query)
    {
        return $query->where('entity_type', self::ENTITY_USER);
    }

    public function scopeForSubAccounts($query)
    {
        return $query->where('entity_type', self::ENTITY_SUB_ACCOUNT);
    }

    protected $casts = [
        'meta'  => 'array', // why: دسترسی آسان به mimes/max/options
        'rules' => 'array',
        'is_required' => 'bool',
    ];
}
