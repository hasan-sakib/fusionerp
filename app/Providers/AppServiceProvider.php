<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
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
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

        // Admin bypasses all Gates — checked before any policy
        Gate::before(function ($user, string $_ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}
