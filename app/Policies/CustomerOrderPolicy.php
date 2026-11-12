<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the customer or user can view the order.
     */
    public function view(Customer|User $user, Order $order): bool
    {
        // If an authenticated staff/admin User is inspecting the order
        if ($user instanceof User) {
            return true;
        }

        // If an authenticated Customer is viewing the order, they MUST own it
        return (int) $order->customer_id === (int) $user->id;
    }

    /**
     * Determine whether the customer can update or cancel the order.
     */
    public function update(Customer|User $user, Order $order): bool
    {
        if ($user instanceof User) {
            return true;
        }

        return (int) $order->customer_id === (int) $user->id;
    }
}
