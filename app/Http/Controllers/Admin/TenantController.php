<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTenantRequest;
use App\Http\Requests\Admin\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total'     => Tenant::withTrashed()->count(),
            'active'    => Tenant::where('status', 'active')->count(),
            'trial'     => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
        ];
        $recent     = Tenant::latest()->limit(5)->get();
        $totalUsers = User::withoutGlobalScopes()->count();

        return view('admin.dashboard', compact('stats', 'recent', 'totalUsers'));
    }

    public function index(): View
    {
        $tenants = Tenant::withTrashed()
            ->orderBy('name')
            ->paginate(25);

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(int $id): View
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $users  = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('admin.tenants.show', compact('tenant', 'users'));
    }

    public function create(): View
    {
        return view('admin.tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        Tenant::create($request->validated());

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => 'suspended']);
        $tenant->delete();

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant suspended and archived.');
    }

    public function restore(int $id): RedirectResponse
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();
        $tenant->update(['status' => 'active']);

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant restored and set to active.');
    }
}
