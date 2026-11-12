<?php

namespace App\Http\Controllers\Api\Finals;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Finals\CustomerCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(
        protected CustomerCartService $cartService
    ) {}

    /**
     * Resolve the current customer instance from Sanctum token or session, or null for guests.
     */
    protected function resolveCustomer(Request $request): ?Customer
    {
        // 1. Check Sanctum Bearer token
        if (auth('sanctum')->check() && auth('sanctum')->user() instanceof Customer) {
            return auth('sanctum')->user();
        }

        // 2. Check customer session guard
        if (auth('customer')->check()) {
            return auth('customer')->user();
        }

        // 3. Fallback to standard request user
        $user = $request->user();
        if ($user instanceof Customer) {
            return $user;
        }

        return null;
    }

    /**
     * View active shopping cart contents and calculated totals.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        $cartDetails = $this->cartService->getCartDetails($customer);

        return response()->json([
            'status' => 'success',
            'data' => $cartDetails,
        ]);
    }

    /**
     * Add a product to the cart (database for logged-in customers, session for guests).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'customization' => ['nullable', 'string', 'max:255'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $customization = $validated['customization'] ?? null;

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $customer = $this->resolveCustomer($request);

        $result = $this->cartService->addItem($customer, $product, $quantity, $customization);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully.',
            'data' => [
                'added_item_id' => $result['id'],
                'cart' => $result['cart'],
            ],
        ], 201);
    }

    /**
     * Update the quantity of a specific item in the cart.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $customer = $this->resolveCustomer($request);
        $cartDetails = $this->cartService->updateQuantity($customer, $id, (int) $validated['quantity']);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item quantity updated.',
            'data' => $cartDetails,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        $cartDetails = $this->cartService->removeItem($customer, $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart.',
            'data' => $cartDetails,
        ]);
    }

    /**
     * Clear all items in the cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        $cartDetails = $this->cartService->clearCart($customer);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully.',
            'data' => $cartDetails,
        ]);
    }
}
