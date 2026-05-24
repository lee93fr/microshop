<?php
// app/Policies/OrderPolicy.php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->user_id === $user->id;
    }

    public function create(User $user): bool { return true; }
    public function update(User $user, Order $order): bool { return $user->isAdmin(); }
    public function delete(User $user, Order $order): bool { return $user->isSuperAdmin(); }

    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            && $order->payment_status !== 'paid'
            && in_array($order->status, ['pending', 'processing']);
    }
}
