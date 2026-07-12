<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->has('tenant')) {
            abort(404);
        }

        $tenant = app('tenant');

        if (! $tenant->isActive()) {
            return response()->view('tenant.suspended', ['tenant' => $tenant], 403);
        }

        return $next($request);
    }
}
