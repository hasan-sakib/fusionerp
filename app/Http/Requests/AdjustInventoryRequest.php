<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', Rule::in(['add', 'subtract', 'set'])],
            'quantity'        => ['required', 'integer', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'adjustment_type.in' => 'Adjustment type must be add, subtract, or set.',
            'quantity.min'       => 'Quantity must be zero or greater.',
        ];
    }
}
