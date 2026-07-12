<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

class SettingService
{
    private const DEFAULTS = [
        'company_name'        => '',
        'company_email'       => '',
        'company_phone'       => '',
        'company_address'     => '',
        'company_website'     => '',
        'timezone'            => 'UTC',
        'currency'            => 'USD',
        'currency_symbol'     => '$',
        'date_format'         => 'Y-m-d',
        'items_per_page'      => 15,
        'order_prefix'        => 'ORD-',
        'default_tax_rate'    => 0,
        'low_stock_threshold' => 10,
        'fiscal_year_start'   => '01',
    ];

    private ?Tenant $tenant;

    public function __construct()
    {
        $this->tenant = app()->has('tenant') ? app('tenant') : null;
    }

    public function all(): array
    {
        $stored = $this->tenant?->settings ?? [];
        return array_merge(self::DEFAULTS, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public function update(array $data): void
    {
        if ($this->tenant === null) {
            return;
        }

        $merged = array_merge($this->tenant->settings ?? [], $data);
        $this->tenant->update(['settings' => $merged]);
        $this->tenant->refresh();

        // Keep the container instance fresh so the same request sees updated values
        app()->instance('tenant', $this->tenant);
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }
}
