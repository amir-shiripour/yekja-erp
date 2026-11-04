<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * @noinspection SqlResolve
 */
class CustomUserField extends Model
{
    use HasFactory;

    public const ENTITY_USER = 'user';
    public const ENTITY_SUB_ACCOUNT = 'sub_account';

    public const CATEGORY_IDENTITY = 'identity';
    public const CATEGORY_CONTACT = 'contact';
    public const CATEGORY_BUSINESS = 'business';
    public const CATEGORY_SYSTEM = 'system';
    public const CATEGORY_GENERAL = 'general';

    protected $fillable = [
        'entity_type',
        'role_name',
        'field_name',
        'label',
        'field_type',
        'category_group',
        'target_scope',
        'sort_order',
        'is_required',
        'rules',
        'meta',
        'options',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_name', 'name');
    }

    public function scopeForUsers(Builder $query): Builder
    {
        return $query->where('entity_type', self::ENTITY_USER);
    }

    public function scopeForSubAccounts(Builder $query): Builder
    {
        return $query->where('entity_type', self::ENTITY_SUB_ACCOUNT);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category_group) {
            self::CATEGORY_IDENTITY => __('resellerhub::messages.profile.categories.identity_title'),
            self::CATEGORY_CONTACT => __('resellerhub::messages.profile.categories.contact_title'),
            self::CATEGORY_BUSINESS => __('resellerhub::messages.profile.categories.business_title'),
            self::CATEGORY_SYSTEM => __('resellerhub::messages.profile.categories.system_title'),
            default => __('resellerhub::messages.profile.categories.general_title'),
        };
    }

    /**
     * Infer or resolve a logical category group from a field name if not explicitly set.
     */
    public static function resolveCategoryGroup(string $fieldName, ?string $explicitGroup = null): string
    {
        if (!empty($explicitGroup) && $explicitGroup !== self::CATEGORY_GENERAL) {
            return $explicitGroup;
        }

        $lower = strtolower($fieldName);

        if (str_contains($lower, 'fax') || str_contains($lower, 'postal') || str_contains($lower, 'post') || str_contains($lower, 'address') || str_contains($lower, 'phone') || str_contains($lower, 'tel') || str_contains($lower, 'landline') || str_contains($lower, 'mobile') || str_contains($lower, 'website') || str_contains($lower, 'url') || str_contains($lower, 'social') || str_contains($lower, 'city')) {
            return self::CATEGORY_CONTACT;
        }

        if (str_contains($lower, 'melli') || str_contains($lower, 'national') || str_contains($lower, 'id') || str_contains($lower, 'kdmly') || str_contains($lower, 'ssn') || str_contains($lower, 'passport') || str_contains($lower, 'registration') || str_contains($lower, 'reg_no') || str_contains($lower, 'legal') || str_contains($lower, 'economic') || str_contains($lower, 'license')) {
            return self::CATEGORY_IDENTITY;
        }

        if (str_contains($lower, 'target') || str_contains($lower, 'goal') || str_contains($lower, 'employee') || str_contains($lower, 'staff') || str_contains($lower, 'revenue') || str_contains($lower, 'sales') || str_contains($lower, 'guild') || str_contains($lower, 'business') || str_contains($lower, 'medical') || str_contains($lower, 'council') || str_contains($lower, 'brand') || str_contains($lower, 'clinic') || str_contains($lower, 'iban') || str_contains($lower, 'bank') || str_contains($lower, 'account') || str_contains($lower, 'hours')) {
            return self::CATEGORY_BUSINESS;
        }

        if (str_contains($lower, 'system') || str_contains($lower, 'quota') || str_contains($lower, 'limit') || str_contains($lower, 'config') || str_contains($lower, 'ip') || str_contains($lower, 'port') || str_contains($lower, 'device') || str_contains($lower, 'sender')) {
            return self::CATEGORY_SYSTEM;
        }

        return self::CATEGORY_GENERAL;
    }

    protected $casts = [
        'meta' => 'array', // why: دسترسی آسان به mimes/max/options
        'rules' => 'array',
        'is_required' => 'bool',
        'sort_order' => 'int',
    ];
}
