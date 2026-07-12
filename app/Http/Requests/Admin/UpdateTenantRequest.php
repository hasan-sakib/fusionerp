<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant')->id;

        return [
            'name'   => ['required', 'string', 'max:255'],
            'slug'   => ['required', 'string', 'max:63', 'regex:/^[a-z0-9-]+$/', Rule::unique('tenants', 'slug')->ignore($tenantId)],
            'status' => ['required', Rule::in(['active', 'trial', 'inactive', 'suspended'])],
            'plan'   => ['nullable', 'string', 'max:100'],
        ];
    }
}
