<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow tests to pre-bind a tenant without subdomain routing
        if (app()->has('tenant')) {
            return $next($request);
        }

        $host      = $request->getHost();
        $parts     = explode('.', $host);
        $subdomain = count($parts) >= 2 ? $parts[0] : null;

        if ($subdomain === null || in_array($subdomain, ['www', 'admin'], true)) {
            abort(404);
        }

        $tenant = Tenant::where('slug', $subdomain)->first();

        if ($tenant === null) {
            abort(404, 'Tenant not found.');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
