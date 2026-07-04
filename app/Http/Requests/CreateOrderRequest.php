<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'       => ['required', 'string', 'max:255'],
            'customer_email'      => ['nullable', 'email', 'max:255'],
            'customer_phone'      => ['nullable', 'string', 'max:50'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'               => ['nullable', 'string'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'At least one product line item is required.',
            'items.min'                   => 'At least one product line item is required.',
            'items.*.product_id.required' => 'Each line item must have a product.',
            'items.*.product_id.exists'   => 'One or more selected products no longer exist.',
            'items.*.quantity.min'        => 'Each item quantity must be at least 1.',
        ];
    }
}
