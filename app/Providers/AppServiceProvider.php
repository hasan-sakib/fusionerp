<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tailwind pagination views
        Paginator::useTailwind();

        // Global password strength rule — uncompromised() check only runs outside tests
        // because it calls the HaveIBeenPwned API which is unavailable in CI
        Password::defaults(function () {
            $rule = Password::min(8)->mixedCase()->numbers()->symbols();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        // Spatie's Role model lives outside App\Models, so auto-discovery won't find this policy
        Gate::policy(Role::class, RolePolicy::class);

        // Admin bypasses all Gates — checked before any policy
        Gate::before(function ($user, string $_ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}
