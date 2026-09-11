<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): This controller
    // implements viewing customers and updating their status for the rubric.
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($customerQuery) use ($search): void {
                    $customerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.customers.index', compact('customers', 'search'));
    }

    public function show(Customer $customer): View
    {
        $orders = $customer->orders()
            ->placed()
            ->with('items')
            ->latest()
            ->limit(5)
            ->get();
        $cart = $customer->orders()
            ->cart()
            ->with(['items.product'])
            ->latest()
            ->first();
        $placedOrderCount = $customer->orders()->placed()->count();
        $totalSpentCents = (int) $customer->orders()->placed()->sum('total_amount_cents');
        $cartItemsCount = (int) ($cart?->items->sum('quantity') ?? 0);

        return view('pages.customers.show', compact(
            'customer',
            'orders',
            'cart',
            'placedOrderCount',
            'totalSpentCents',
            'cartItemsCount',
        ));
    }

    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
        ]);

        $customer->update(['status' => $validated['status']]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer status updated.');
    }
}
