<?php

namespace App\Http\Controllers\Finals;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Finals\CustomerCartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerStorefrontController extends Controller
{
    public function __construct(
        protected CustomerCartService $cartService
    ) {}

    /**
     * Resolve current customer from session guard.
     */
    protected function getCustomer(): ?Customer
    {
        return Auth::guard('customer')->user();
    }

    /**
     * Display the main customer storefront and live product catalog.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $query = Product::query()
            ->where('is_active', true)
            ->with('cakePreviews');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->get();

        $customer = $this->getCustomer();
        $cartDetails = $this->cartService->getCartDetails($customer);

        return view('finals.storefront', compact('products', 'search', 'category', 'cartDetails', 'customer'));
    }

    /**
     * Display the customer's dedicated shopping cart page.
     */
    public function cart(Request $request): View
    {
        $customer = $this->getCustomer();
        $cartDetails = $this->cartService->getCartDetails($customer);

        return view('finals.cart', compact('cartDetails', 'customer'));
    }

    /**
     * Web endpoint to add an item to the cart (supports both AJAX JSON and standard form POST).
     */
    public function addToCart(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'customization' => ['nullable', 'string', 'max:255'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $customization = $validated['customization'] ?? null;

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $customer = $this->getCustomer();

        $result = $this->cartService->addItem($customer, $product, $quantity, $customization);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart successfully.',
                'data' => $result,
            ]);
        }

        return back()->with('success', "{$product->name} added to cart!");
    }

    /**
     * Web endpoint to update quantity of an item in the cart.
     */
    public function updateCartItem(Request $request, string|int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $customer = $this->getCustomer();
        $cart = $this->cartService->updateQuantity($customer, $id, (int) $validated['quantity']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cart updated.',
                'data' => $cart,
            ]);
        }

        return back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Web endpoint to remove an item from the cart.
     */
    public function removeCartItem(Request $request, string|int $id): JsonResponse|RedirectResponse
    {
        $customer = $this->getCustomer();
        $cart = $this->cartService->removeItem($customer, $id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from cart.',
                'data' => $cart,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Web endpoint to clear the entire cart.
     */
    public function clearCart(Request $request): JsonResponse|RedirectResponse
    {
        $customer = $this->getCustomer();
        $cart = $this->cartService->clearCart($customer);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cart cleared.',
                'data' => $cart,
            ]);
        }

        return back()->with('success', 'Cart cleared successfully.');
    }

    /**
     * Display the checkout confirmation page.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $customer = $this->getCustomer();

        if (! $customer) {
            return redirect()->route('finals.login')
                ->with('warning', 'Please log in or register before checking out.');
        }

        $cartDetails = $this->cartService->getCartDetails($customer);

        if (empty($cartDetails['items'])) {
            return redirect()->route('finals.cart')
                ->with('error', 'Your shopping cart is empty.');
        }

        return view('finals.checkout', compact('cartDetails', 'customer'));
    }

    /**
     * Display the customer's placed orders list.
     */
    public function orders(Request $request): View|RedirectResponse
    {
        $customer = $this->getCustomer();

        if (! $customer) {
            return redirect()->route('finals.login')
                ->with('warning', 'Please log in to view your orders.');
        }

        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->placed()
            ->with(['items.product'])
            ->latest()
            ->get();

        return view('finals.orders.index', compact('orders', 'customer'));
    }

    /**
     * Display the detail tracking view for a specific order.
     */
    public function orderDetails(Request $request, int $id): View|RedirectResponse
    {
        $customer = $this->getCustomer();

        if (! $customer) {
            return redirect()->route('finals.login')
                ->with('warning', 'Please log in to view order details.');
        }

        /** @var Order $order */
        $order = Order::query()
            ->with(['items.product', 'customer'])
            ->findOrFail($id);

        // Enforce customer data privacy (Rubric 5)
        if ((int) $order->customer_id !== (int) $customer->id) {
            abort(403, 'Forbidden: You do not have permission to view another customer\'s orders.');
        }

        return view('finals.orders.show', compact('order', 'customer'));
    }
}
