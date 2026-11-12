<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): This controller keeps
    // cart actions separate from the read-only placed-order page.
    public function index(): View
    {
        $carts = Order::query()
            ->cart()
            ->with(['customer', 'items'])
            ->latest()
            ->get();

        return view('pages.cart.index', compact('carts'));
    }

    /**
     * Add one configured product to the existing draft cart.
     */
    public function storeItem(Request $request): RedirectResponse
    {
        $options = config('cake.options');
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'size' => ['required', 'string'],
            'filling' => ['required', 'string'],
            'frosting' => ['required', 'string'],
            'decoration' => ['required', 'string'],
            'toppings' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:80'],
        ]);

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $customer = Customer::query()->findOrFail($validated['customer_id']);
        $cart = Order::query()
            ->where('customer_id', $customer->id)
            ->cart()
            ->latest()
            ->first();

        if (! $cart) {
            $cart = Order::create([
                'order_number' => 'SC-'.str_pad((string) (Order::max('id') + 1001), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'status' => Order::CART_STATUS,
                'total_amount_cents' => 0,
            ]);
        }
        $toppingCodes = array_values(array_filter(explode(',', (string) ($validated['toppings'] ?? ''))));
        $allowedToppingCodes = array_column($options['toppings'], 'code');

        abort_unless(
            in_array($validated['size'], array_column($options['sizes'], 'code'), true)
                && in_array($validated['filling'], array_column($options['fillings'], 'code'), true)
                && in_array($validated['frosting'], array_column($options['frostings'], 'code'), true)
                && in_array($validated['decoration'], array_column($options['decorations'], 'code'), true)
                && count(array_diff($toppingCodes, $allowedToppingCodes)) === 0,
            422,
        );

        $optionCodes = [
            'sizes' => [$validated['size']],
            'fillings' => [$validated['filling']],
            'frostings' => [$validated['frosting']],
            'decorations' => [$validated['decoration']],
            'toppings' => $toppingCodes,
        ];
        $unitPriceCents = $product->base_price_cents;
        $summaryParts = [];

        foreach ($optionCodes as $group => $codes) {
            foreach ($options[$group] as $option) {
                if (in_array($option['code'], $codes, true)) {
                    $unitPriceCents += $option['price_adjustment_cents'];
                    $summaryParts[] = $option['label'];
                }
            }
        }

        if (($validated['message'] ?? '') !== '') {
            $summaryParts[] = 'Message: '.$validated['message'];
        }

        $quantity = $validated['quantity'];
        $cart->items()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => $quantity,
            'unit_price_cents' => $unitPriceCents,
            'line_total_cents' => $unitPriceCents * $quantity,
            'customization_summary' => implode(' · ', $summaryParts),
        ]);
        $cart->update(['total_amount_cents' => $cart->items()->sum('line_total_cents')]);

        return redirect()->route('cart.show', $cart)->with('success', 'Product added to the current cart.');
    }

    public function show(Order $cart): View
    {
        abort_unless($cart->isCart(), 404);

        $cart->load(['customer', 'items.product']);

        return view('pages.cart.show', compact('cart'));
    }

    public function submit(Order $cart): RedirectResponse
    {
        abort_unless($cart->isCart(), 404);
        abort_if(! $cart->items()->exists(), 422, 'A cart must contain at least one product before it can be submitted.');

        $cart->update(['status' => 'pending']);

        return redirect()->route('orders.show', $cart)->with('success', 'Cart submitted as a placed order.');
    }

    /**
     * Change the quantity of one item in a draft cart.
     */
    public function updateQuantity(Request $request, Order $cart, OrderItem $orderItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        abort_unless($cart->isCart(), 404);

        if ($orderItem->order_id !== $cart->id) {
            throw (new ModelNotFoundException)->setModel(OrderItem::class, [$orderItem->id]);
        }

        DB::transaction(function () use ($cart, $orderItem, $validated): void {
            $orderItem->update([
                'quantity' => $validated['quantity'],
                'line_total_cents' => $orderItem->unit_price_cents * $validated['quantity'],
            ]);
            $cart->update([
                'total_amount_cents' => $cart->items()->sum('line_total_cents'),
            ]);
        });

        return redirect()->route('cart.show', $cart)->with('success', 'Cart quantity updated.');
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
