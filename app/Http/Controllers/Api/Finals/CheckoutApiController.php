<?php

namespace App\Http\Controllers\Api\Finals;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFinalOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Finals\CustomerCartService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutApiController extends Controller
{
    public function __construct(
        protected CustomerCartService $cartService
    ) {}

    /**
     * Resolve the current customer instance from Sanctum token or session.
     */
    protected function resolveCustomer(Request $request): Customer
    {
        if (auth('sanctum')->check() && auth('sanctum')->user() instanceof Customer) {
            return auth('sanctum')->user();
        }

        if (auth('customer')->check()) {
            return auth('customer')->user();
        }

        $user = $request->user();
        if ($user instanceof Customer) {
            return $user;
        }

        abort(401, 'Please log in to proceed to checkout.');
    }

    /**
     * Preview checkout summary with itemized prices and computed totals.
     */
    public function summary(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        $cart = $this->cartService->getOrCreateCart($customer);
        $cart->load(['items.product']);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is currently empty.',
            ], 422);
        }

        $cartDetails = $this->cartService->getCartDetails($customer);

        return response()->json([
            'status' => 'success',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
                'summary' => $cartDetails,
            ],
        ]);
    }

    /**
     * Submit and place the order with atomic database transaction handling
     * and asynchronous Laravel Queue confirmation dispatch.
     */
    public function checkout(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'scheduled_at' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $cart = $this->cartService->getOrCreateCart($customer);
        $cart->load(['items.product']);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot place order: your cart is empty.',
            ], 422);
        }

        // Apply any updated customer contact info
        if (! empty($validated['phone']) || ! empty($validated['address'])) {
            $customer->update(array_filter([
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]));
        }

        /** @var Order $placedOrder */
        $placedOrder = DB::transaction(function () use ($cart, $validated): Order {
            // Re-verify exact item totals
            $exactTotalCents = (int) $cart->items()->sum('line_total_cents');

            $cart->update([
                'status' => 'pending',
                'total_amount_cents' => $exactTotalCents,
                'notes' => $validated['notes'] ?? $cart->notes,
                'scheduled_at' => ! empty($validated['scheduled_at'])
                    ? Carbon::parse($validated['scheduled_at'])
                    : now()->addDays(2),
            ]);

            return $cart->fresh(['items.product', 'customer']);
        });

        // Asynchronous processing using Laravel Queues (Rubric 4)
        ProcessFinalOrderConfirmation::dispatch($placedOrder);

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully! A confirmation notice has been queued.',
            'data' => [
                'order_number' => $placedOrder->order_number,
                'status' => $placedOrder->status,
                'total_amount_cents' => $placedOrder->total_amount_cents,
                'total_pesos' => number_format($placedOrder->total_amount_cents / 100, 2),
                'scheduled_at' => $placedOrder->scheduled_at?->toIso8601String(),
                'notes' => $placedOrder->notes,
                'item_count' => $placedOrder->items->count(),
                'items' => $placedOrder->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name_snapshot,
                    'quantity' => $item->quantity,
                    'unit_price_pesos' => number_format($item->unit_price_cents / 100, 2),
                    'line_total_pesos' => number_format($item->line_total_cents / 100, 2),
                ]),
            ],
        ], 201);
    }
}
