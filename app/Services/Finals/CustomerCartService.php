<?php

namespace App\Services\Finals;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;

class CustomerCartService
{
    public const SESSION_KEY = 'finals_cart';

    /**
     * Get or create the active draft cart for a logged-in customer in the database.
     */
    public function getOrCreateCart(Customer $customer): Order
    {
        $cart = Order::query()
            ->where('customer_id', $customer->id)
            ->where('status', Order::CART_STATUS)
            ->latest()
            ->first();

        if (! $cart) {
            $cart = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'status' => Order::CART_STATUS,
                'total_amount_cents' => 0,
            ]);
        }

        return $cart;
    }

    /**
     * Get full details for the cart (supports both authenticated customer DB cart and guest session cart).
     */
    public function getCartDetails(?Customer $customer = null): array
    {
        if ($customer) {
            return $this->getDatabaseCartDetails($customer);
        }

        return $this->getSessionCartDetails();
    }

    /**
     * Get database cart details for an authenticated customer.
     */
    protected function getDatabaseCartDetails(Customer $customer): array
    {
        $cart = $this->getOrCreateCart($customer);
        $cart->load(['items.product']);

        $items = $cart->items;
        $totalCents = (int) $items->sum('line_total_cents');

        if ($cart->total_amount_cents !== $totalCents) {
            $cart->update(['total_amount_cents' => $totalCents]);
        }

        return [
            'cart_id' => $cart->id,
            'order_number' => $cart->order_number,
            'status' => $cart->status,
            'is_guest' => false,
            'item_count' => (int) $items->sum('quantity'),
            'total_amount_cents' => $totalCents,
            'total_pesos' => number_format($totalCents / 100, 2),
            'items' => $items->map(function (OrderItem $item): array {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name_snapshot,
                    'quantity' => (int) $item->quantity,
                    'unit_price_cents' => (int) $item->unit_price_cents,
                    'unit_price_pesos' => number_format($item->unit_price_cents / 100, 2),
                    'line_total_cents' => (int) $item->line_total_cents,
                    'line_total_pesos' => number_format($item->line_total_cents / 100, 2),
                    'customization_summary' => $item->customization_summary,
                    'image_path' => $item->product?->image_path,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Get session-based cart details for a guest visitor.
     */
    protected function getSessionCartDetails(): array
    {
        $cart = session()->get(self::SESSION_KEY, []);
        $items = collect($cart);

        $totalCents = (int) $items->sum('line_total_cents');
        $itemCount = (int) $items->sum('quantity');

        return [
            'cart_id' => 'guest-session',
            'order_number' => 'GUEST-CART',
            'status' => 'cart',
            'is_guest' => true,
            'item_count' => $itemCount,
            'total_amount_cents' => $totalCents,
            'total_pesos' => number_format($totalCents / 100, 2),
            'items' => $items->values()->all(),
        ];
    }

    /**
     * Add a product to the cart (database if customer logged in, session if guest).
     */
    public function addItem(?Customer $customer, Product $product, int $quantity = 1, ?string $customization = null): array
    {
        if ($customer) {
            $item = $this->addDatabaseItem($customer, $product, $quantity, $customization);
            return [
                'id' => $item->id,
                'cart' => $this->getDatabaseCartDetails($customer),
            ];
        }

        return $this->addSessionItem($product, $quantity, $customization);
    }

    /**
     * Add item to authenticated customer database cart.
     */
    protected function addDatabaseItem(Customer $customer, Product $product, int $quantity, ?string $customization): OrderItem
    {
        $cart = $this->getOrCreateCart($customer);

        /** @var OrderItem|null $existing */
        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('customization_summary', $customization)
            ->first();

        $unitPriceCents = (int) $product->base_price_cents;

        if ($existing) {
            $newQuantity = $existing->quantity + $quantity;
            $existing->update([
                'quantity' => $newQuantity,
                'line_total_cents' => $newQuantity * $unitPriceCents,
            ]);
            $item = $existing;
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'quantity' => $quantity,
                'unit_price_cents' => $unitPriceCents,
                'line_total_cents' => $quantity * $unitPriceCents,
                'customization_summary' => $customization,
            ]);
        }

        $this->refreshCartTotal($cart);

        return $item;
    }

    /**
     * Add item to guest session cart.
     */
    protected function addSessionItem(Product $product, int $quantity, ?string $customization): array
    {
        $cart = session()->get(self::SESSION_KEY, []);
        $itemKey = (string) $product->id . ($customization ? '-' . md5($customization) : '');
        $unitPriceCents = (int) $product->base_price_cents;

        if (isset($cart[$itemKey])) {
            $newQuantity = $cart[$itemKey]['quantity'] + $quantity;
            $cart[$itemKey]['quantity'] = $newQuantity;
            $cart[$itemKey]['line_total_cents'] = $newQuantity * $unitPriceCents;
            $cart[$itemKey]['line_total_pesos'] = number_format(($newQuantity * $unitPriceCents) / 100, 2);
        } else {
            $cart[$itemKey] = [
                'id' => $itemKey,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price_cents' => $unitPriceCents,
                'unit_price_pesos' => number_format($unitPriceCents / 100, 2),
                'line_total_cents' => $quantity * $unitPriceCents,
                'line_total_pesos' => number_format(($quantity * $unitPriceCents) / 100, 2),
                'customization_summary' => $customization,
                'image_path' => $product->image_path,
            ];
        }

        session()->put(self::SESSION_KEY, $cart);

        return [
            'id' => $itemKey,
            'cart' => $this->getSessionCartDetails(),
        ];
    }

    /**
     * Update the quantity of a specific item in the cart.
     */
    public function updateQuantity(?Customer $customer, int|string $itemId, int $quantity): array
    {
        if ($customer) {
            $cart = $this->getOrCreateCart($customer);
            /** @var OrderItem|null $item */
            $item = $cart->items()->where('id', $itemId)->first();

            if ($item) {
                if ($quantity <= 0) {
                    $item->delete();
                } else {
                    $item->update([
                        'quantity' => $quantity,
                        'line_total_cents' => $quantity * $item->unit_price_cents,
                    ]);
                }
                $this->refreshCartTotal($cart);
            }

            return $this->getDatabaseCartDetails($customer);
        }

        // Guest session update
        $cart = session()->get(self::SESSION_KEY, []);
        $itemKey = (string) $itemId;

        if (isset($cart[$itemKey])) {
            if ($quantity <= 0) {
                unset($cart[$itemKey]);
            } else {
                $unitPriceCents = $cart[$itemKey]['unit_price_cents'];
                $cart[$itemKey]['quantity'] = $quantity;
                $cart[$itemKey]['line_total_cents'] = $quantity * $unitPriceCents;
                $cart[$itemKey]['line_total_pesos'] = number_format(($quantity * $unitPriceCents) / 100, 2);
            }
            session()->put(self::SESSION_KEY, $cart);
        }

        return $this->getSessionCartDetails();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(?Customer $customer, int|string $itemId): array
    {
        if ($customer) {
            $cart = $this->getOrCreateCart($customer);
            $cart->items()->where('id', $itemId)->delete();
            $this->refreshCartTotal($cart);

            return $this->getDatabaseCartDetails($customer);
        }

        // Guest session remove
        $cart = session()->get(self::SESSION_KEY, []);
        $itemKey = (string) $itemId;

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            session()->put(self::SESSION_KEY, $cart);
        }

        return $this->getSessionCartDetails();
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart(?Customer $customer): array
    {
        if ($customer) {
            $cart = $this->getOrCreateCart($customer);
            $cart->items()->delete();
            $this->refreshCartTotal($cart);

            return $this->getDatabaseCartDetails($customer);
        }

        session()->forget(self::SESSION_KEY);

        return $this->getSessionCartDetails();
    }

    /**
     * Merge any existing guest session cart into an authenticated customer's database cart.
     */
    public function mergeSessionCartIntoCustomer(Customer $customer): void
    {
        $sessionCart = session()->get(self::SESSION_KEY, []);

        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->is_active) {
                $this->addDatabaseItem(
                    $customer,
                    $product,
                    (int) $item['quantity'],
                    $item['customization_summary'] ?? null
                );
            }
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * Recalculate and update the total amount of a database cart.
     */
    public function refreshCartTotal(Order $cart): void
    {
        $totalCents = (int) $cart->items()->sum('line_total_cents');
        $cart->update(['total_amount_cents' => $totalCents]);
    }
}
