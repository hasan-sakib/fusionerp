<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
            'sku'             => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'barcode'         => ['nullable', 'string', 'max:100'],
            'description'     => ['nullable', 'string'],
            'price'           => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'cost'            => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'status'          => ['required', Rule::in(['active', 'inactive', 'draft'])],
            'is_featured'     => ['nullable', 'boolean'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
