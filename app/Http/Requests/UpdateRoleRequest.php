<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware `can:roles.edit` handles authorization
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name'          => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId), 'regex:/^[a-z][a-z0-9_-]*$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Role name must start with a letter and contain only lowercase letters, numbers, hyphens, and underscores.',
        ];
    }
}
