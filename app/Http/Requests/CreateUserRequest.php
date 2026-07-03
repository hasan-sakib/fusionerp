<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware `can:users.create` handles authorization
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'department'            => ['nullable', 'string', 'max:100'],
            'position'              => ['nullable', 'string', 'max:100'],
            'status'                => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'role'                  => ['required', 'string', Rule::exists('roles', 'name')],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
