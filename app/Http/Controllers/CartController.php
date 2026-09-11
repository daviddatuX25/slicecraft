<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): The scored cart
    // actions live in a dedicated controller while OrderController is read-only.
    public function index(): View
    {
        $cart = Order::query()
            ->cart()
            ->with(['customer', 'items'])
            ->latest()
            ->first();

        return view('pages.cart.index', compact('cart'));
    }

    public function show(Order $cart): View
    {
        abort_unless($cart->isCart(), 404);

        $cart->load(['customer', 'items.product']);

        return view('pages.cart.show', compact('cart'));
    }

    public function destroyItem(Order $cart, OrderItem $orderItem): RedirectResponse
    {
        abort_unless($cart->isCart(), 404);

        if ($orderItem->order_id !== $cart->id) {
            throw (new ModelNotFoundException)->setModel(OrderItem::class, [$orderItem->id]);
        }

        DB::transaction(function () use ($cart, $orderItem): void {
            $orderItem->delete();
            $cart->update([
                'total_amount_cents' => $cart->items()->sum('line_total_cents'),
            ]);
        });

        return redirect()->route('cart.show', $cart)->with('success', 'Cart product removed.');
    }
}
