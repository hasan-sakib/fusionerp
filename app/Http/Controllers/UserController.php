<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('department', 'like', $term)
                      ->orWhere('position', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $request->role)))
            ->when(
                $request->boolean('trashed') && auth()->user()->hasRole('admin'),
                fn ($q) => $q->onlyTrashed()
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->only([
            'name', 'email', 'phone', 'department', 'position', 'status', 'password',
        ]));

        // Admin-created accounts are pre-verified — the email address is known to be valid
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->assignRole($request->role);

        return redirect()
            ->route('users.show', $user)
            ->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load('roles');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $originalEmail = $user->email;

        // safe()->except() returns a plain PHP array in Laravel 12
        $user->fill($request->safe()->except(['role']));

        // Must compare against $originalEmail — after fill(), $user->email is already the new value
        if ($request->email !== $originalEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Replace all existing roles with the single selected role
        $user->syncRoles([$request->role]);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion regardless of admin bypass via Gate::before
        abort_if(auth()->id() === $user->id, 403, 'You cannot delete your own account.');

        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name} has been deactivated.");
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $this->authorize('restore', $user);

        $user->restore();

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name} has been restored.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $status = Password::sendResetLink(['email' => $user->email]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            __($status)
        );
    }
}
