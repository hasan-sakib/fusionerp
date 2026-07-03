<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.delete');
    }

    public function restore(User $user, User $model): bool
    {
        // Gate::before grants admin unconditional access; this only runs for non-admins
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
