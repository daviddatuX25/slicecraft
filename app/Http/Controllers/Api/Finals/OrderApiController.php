<?php

namespace App\Http\Controllers\Api\Finals;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * Resolve the current authenticated customer or staff user.
     */
    protected function resolveCustomerOrUser(Request $request): Customer|User
    {
        if ($request->bearerToken()) {
            if ($tokenUser = auth('sanctum')->user()) {
                return $tokenUser;
            }
        }

        if (auth('sanctum')->check()) {
            return auth('sanctum')->user();
        }

        if (auth('customer')->check()) {
            return auth('customer')->user();
        }

        if (auth('web')->check()) {
            return auth('web')->user();
        }

        $user = $request->user();
        if ($user instanceof Customer || $user instanceof User) {
            return $user;
        }

        abort(401, 'Unauthorized. Please authenticate to access orders.');
    }

    /**
     * Display a listing of orders belonging strictly to the authenticated customer.
     * Enforces customer data privacy (Rubric 5).
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $this->resolveCustomerOrUser($request);

        $query = Order::query()
            ->placed()
            ->with(['items.product', 'customer'])
            ->latest();

        // Customer sees strictly their own orders
        if ($actor instanceof Customer) {
            $query->where('customer_id', $actor->id);
        }

        $orders = $query->get();

        $formatted = $orders->map(function (Order $order): array {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount_cents' => (int) $order->total_amount_cents,
                'total_pesos' => number_format($order->total_amount_cents / 100, 2),
                'scheduled_at' => $order->scheduled_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'item_count' => $order->items->sum('quantity'),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name_snapshot,
                    'quantity' => $item->quantity,
                    'unit_price_pesos' => number_format($item->unit_price_cents / 100, 2),
                    'line_total_pesos' => number_format($item->line_total_cents / 100, 2),
                ]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $formatted->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Display the specified order details with strict customer privacy protection.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $actor = $this->resolveCustomerOrUser($request);

        /** @var Order $order */
        $order = Order::query()
            ->with(['items.product', 'customer'])
            ->findOrFail($id);

        // Privacy Check: A customer cannot inspect another customer's order records (Rubric 5)
        if ($actor instanceof Customer && (int) $order->customer_id !== (int) $actor->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You do not have permission to view another customer\'s order records.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer' => [
                    'name' => $order->customer?->name,
                    'email' => $order->customer?->email,
                    'phone' => $order->customer?->phone,
                    'address' => $order->customer?->address,
                ],
                'total_amount_cents' => (int) $order->total_amount_cents,
                'total_pesos' => number_format($order->total_amount_cents / 100, 2),
                'notes' => $order->notes,
                'scheduled_at' => $order->scheduled_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name_snapshot,
                    'quantity' => (int) $item->quantity,
                    'unit_price_pesos' => number_format($item->unit_price_cents / 100, 2),
                    'line_total_pesos' => number_format($item->line_total_cents / 100, 2),
                    'customization_summary' => $item->customization_summary,
                ]),
            ],
        ]);
    }
}
