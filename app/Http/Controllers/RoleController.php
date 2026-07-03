<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const BUILT_IN = ['admin', 'manager', 'employee'];

    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->user_count = User::role($role->name)->count();
                return $role;
            });

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => Str::before($p->name, '.'));

        return view('roles.create', compact('permissions'));
    }

    public function store(CreateRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('roles.show', $role)
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        $permissions = $role->permissions
            ->groupBy(fn (Permission $p) => Str::before($p->name, '.'));

        $users = User::role($role->name)->get();

        return view('roles.show', compact('role', 'permissions', 'users'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $allPermissions = Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => Str::before($p->name, '.'));

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'allPermissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        // Built-in roles may have permissions updated, but their names are locked
        if (! in_array($role->name, self::BUILT_IN)) {
            $role->update(['name' => $request->name]);
        }

        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if (in_array($role->name, self::BUILT_IN)) {
            return back()->with('error', "The '{$role->name}' role is built-in and cannot be deleted.");
        }

        $userCount = User::role($role->name)->count();
        if ($userCount > 0) {
            return back()->with('error', "Cannot delete '{$role->name}' — {$userCount} user(s) still have this role.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Role '{$roleName}' deleted.");
    }
}
