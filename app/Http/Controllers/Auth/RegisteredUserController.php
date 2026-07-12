<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'confirmed', Password::defaults()],
        ]);

        // Derive a unique slug from company name
        $baseSlug = Str::slug($request->string('company_name')->toString());
        $slug     = $baseSlug;
        $i        = 2;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        $tenant = Tenant::create([
            'name'   => $request->company_name,
            'slug'   => $slug,
            'status' => 'trial',
        ]);

        // Bind so BelongsToTenant sets tenant_id automatically and TenantScope applies
        app()->instance('tenant', $tenant);

        $user = User::create([
            'tenant_id'         => $tenant->id,
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => $request->password,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        // First registered user of the tenant becomes admin
        $user->assignRole('admin');

        event(new Registered($user));

        Auth::login($user);

        session()->flash('subdomain_url', $tenant->subdomainUrl());

        return redirect()->route('dashboard');
    }
}
