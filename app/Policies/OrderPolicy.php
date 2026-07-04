<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('orders.edit') && $order->isEditable();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('orders.delete');
    }

    public function process(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('orders.process');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('orders.cancel') && $order->isCancellable();
    }
}
