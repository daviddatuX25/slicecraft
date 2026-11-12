<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): This page shows placed
    // orders and does not change the editable cart.
    public function index(): View
    {
        $orders = Order::query()
            ->placed()
            ->with(['customer', 'items'])
            ->latest()
            ->paginate(10);

        return view('pages.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->isPlaced(), 404);

        $order->load(['customer', 'items.product']);

        return view('pages.orders.show', compact('order'));
    }
}
