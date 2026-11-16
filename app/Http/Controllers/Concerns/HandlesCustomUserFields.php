<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CustomUserField;
use App\Models\User;
use App\Models\UserCustomValue;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait HandlesCustomUserFields
{
    protected function fieldsForRoles(array $roleNames): Collection
    {
        return CustomUserField::whereIn('role_name', $roleNames)->orderBy('id')->get()->unique('field_name');
    }

    protected function dynamicRules(Collection $fields): array
    {
        $rules = [];

        foreach ($fields as $f) {
            $key  = 'custom.' . $f->field_name;
            $type = strtolower($f->field_type ?? 'text');
            $meta = is_array($f->meta ?? null) ? $f->meta : (is_string($f->meta ?? null) ? json_decode($f->meta, true) : []);
            $meta = $meta ?: [];

            $base = ($f->is_required ?? false) ? ['required'] : ['nullable'];

            switch ($type) {
                case 'number':
                    $base[] = 'numeric';
                    break;
                case 'date':
                    $base[] = 'date';
                    break;
                case 'email':
                    $base[] = 'email';
                    break;
                case 'file':
                    $fileRules = ['file'];
                    if (!empty($meta['mimes']) && is_array($meta['mimes'])) {
                        $fileRules[] = 'mimes:' . implode(',', $meta['mimes']);
                    } elseif (!empty($meta['mimes']) && is_string($meta['mimes'])) {
                        $fileRules[] = 'mimes:' . $meta['mimes'];
                    }
                    if (!empty($meta['max'])) {
                        $fileRules[] = 'max:' . intval($meta['max']);
                    }
                    $base = array_merge($base, $fileRules);
                    break;
                case 'checkbox':
                    $base[] = 'array';
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base['custom.' . $f->field_name . '.*'] = ['in:' . implode(',', array_values($meta['options']))];
                    }
                    break;
                case 'select':
                case 'radio':
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base[] = 'in:' . implode(',', array_values($meta['options']));
                    } else {
                        $base[] = 'string';
                    }
                    break;
                default:
                    $base[] = 'string';
            }

            $rules[$key] = $base;

            if (!empty($f->rules) && is_array($f->rules)) {
                $rules[$key] = array_merge($rules[$key], $f->rules);
            }
        }

        return $rules;
    }

    protected function persistCustomValues(User $user, Collection $fields, Request $request): void
    {
        foreach ($fields as $f) {
            $type = strtolower($f->field_type ?? 'text');

            if ($type === 'file') {
                $file = $request->file('custom.' . $f->field_name);
                if ($file) {
                    $prev = UserCustomValue::where('user_id', $user->id)
                        ->where('field_name', $f->field_name)
                        ->first();

                    if ($prev && $prev->value && Storage::disk('public')->exists($prev->value)) {
                        Storage::disk('public')->delete($prev->value);
                    }

                    $path = $file->store('user-custom', 'public');

                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $f->field_name],
                        ['value' => $path],
                    );
                } else {
                    if (!UserCustomValue::where('user_id', $user->id)->where('field_name', $f->field_name)->exists()) {
                        UserCustomValue::create([
                            'user_id'    => $user->id,
                            'field_name' => $f->field_name,
                            'value'      => null,
                        ]);
                    }
                }
                continue;
            }

            $raw = $request->input('custom.' . $f->field_name);

            if ($type === 'checkbox' && is_array($raw)) {
                $value = json_encode(array_values($raw), JSON_UNESCAPED_UNICODE);
            } else {
                $value = $raw;
            }

            UserCustomValue::updateOrCreate(
                ['user_id' => $user->id, 'field_name' => $f->field_name],
                ['value' => $value],
            );
        }
    }
}
