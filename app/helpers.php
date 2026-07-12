<?php

declare(strict_types=1);

use App\Models\Tenant;

if (!function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        return app()->has('tenant') ? app('tenant') : null;
    }
}

if (!function_exists('tenantId')) {
    function tenantId(): ?int
    {
        return tenant()?->id;
    }
}
