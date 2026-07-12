<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.edit');
    }

    public function rules(): array
    {
        return [
            'company_name'        => ['required', 'string', 'max:255'],
            'company_email'       => ['nullable', 'email', 'max:255'],
            'company_phone'       => ['nullable', 'string', 'max:50'],
            'company_address'     => ['nullable', 'string', 'max:500'],
            'company_website'     => ['nullable', 'url', 'max:255'],
            'timezone'            => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'currency'            => ['required', 'string', 'max:10'],
            'currency_symbol'     => ['required', 'string', 'max:5'],
            'date_format'         => ['required', Rule::in(['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'])],
            'items_per_page'      => ['required', 'integer', Rule::in([10, 15, 25, 50])],
            'order_prefix'        => ['required', 'string', 'max:20'],
            'default_tax_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:10000'],
            'fiscal_year_start'   => ['required', Rule::in(['01','02','03','04','05','06','07','08','09','10','11','12'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name'        => 'company name',
            'company_email'       => 'company email',
            'company_phone'       => 'company phone',
            'company_address'     => 'company address',
            'company_website'     => 'company website',
            'currency_symbol'     => 'currency symbol',
            'date_format'         => 'date format',
            'items_per_page'      => 'items per page',
            'order_prefix'        => 'order prefix',
            'default_tax_rate'    => 'default tax rate',
            'low_stock_threshold' => 'low stock threshold',
            'fiscal_year_start'   => 'fiscal year start',
        ];
    }
}
