<?php

namespace Database\Factories;

use App\Models\CustomUserField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomUserField>
 */
class CustomUserFieldFactory extends Factory
{
    protected $model = CustomUserField::class;

    public function definition(): array
    {
        return [
            'entity_type' => CustomUserField::ENTITY_SUB_ACCOUNT,
            'role_name' => 'sub_account',
            'field_name' => $this->faker->unique()->word,
            'label' => $this->faker->words(2, true),
            'field_type' => 'text',
            'category_group' => CustomUserField::CATEGORY_GENERAL,
            'target_scope' => 'user',
            'sort_order' => 0,
            'is_required' => false,
            'rules' => [],
            'meta' => null,
        ];
    }
}
