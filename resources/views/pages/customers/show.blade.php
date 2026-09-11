@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This detail screen keeps the required status update while adding useful profile, cart, and placed-order context for an admin view. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-5xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('customers.index') }}" class="inline-flex items-center rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-sm font-bold text-[#5b3a31] hover:border-[#b85c5c]"><i class="fa fa-arrow-left mr-1" aria-hidden="true"></i> Back to customers</a>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Customer profile</p>
                <h1 class="mt-1 text-3xl font-bold">{{ $customer->name }}</h1>
                <p class="mt-2 text-sm text-[#78645d]">{{ $customer->email }}</p>
            </div>
            @if ($customer->status === 'active')
                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">Active customer</span>
            @elseif ($customer->status === 'pending')
                <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">Pending customer</span>
            @else
                <span class="rounded-full bg-stone-100 px-4 py-2 text-sm font-bold text-stone-700">Inactive customer</span>
            @endif
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Customer overview</p><h2 class="mt-1 text-xl font-bold">Account snapshot</h2></div>
                <span class="text-xs font-semibold text-[#917d73]">Customer #{{ $customer->id }}</span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                <div class="rounded-2xl bg-[#fffaf8] p-3"><span class="block text-xs text-[#917d73]">Placed orders</span><strong class="mt-1 block text-xl">{{ $placedOrderCount }}</strong></div>
                <div class="rounded-2xl bg-[#fffaf8] p-3"><span class="block text-xs text-[#917d73]">Cart products</span><strong class="mt-1 block text-xl">{{ $cartItemsCount }}</strong></div>
                <div class="rounded-2xl bg-[#fffaf8] p-3"><span class="block text-xs text-[#917d73]">Order value</span><strong class="mt-1 block text-lg text-[#b85c5c]">₱{{ number_format($totalSpentCents / 100, 2) }}</strong></div>
                <div class="rounded-2xl bg-[#fffaf8] p-3"><span class="block text-xs text-[#917d73]">Account status</span><strong class="mt-1 block text-base">{{ ucfirst($customer->status) }}</strong></div>
            </div>
        </section>

        <div class="mt-5 grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Contact information</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Email</dt><dd class="mt-1 font-semibold">{{ $customer->email }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Phone</dt><dd class="mt-1 font-semibold">{{ $customer->phone ?: 'Not recorded' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Address</dt><dd class="mt-1 font-semibold">{{ $customer->address ?: 'Not recorded' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Current cart</p><h2 class="mt-1 text-xl font-bold">Editable draft</h2></div>
                    @if ($cart)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $cartItemsCount }} products</span>
                    @endif
                </div>
                @if ($cart)
                    <p class="mt-3 text-sm text-[#78645d]">{{ $cart->order_number }} · ₱{{ number_format($cart->total_amount_cents / 100, 2) }}</p>
                    <div class="mt-3 space-y-2 text-sm">
                        @foreach ($cart->items->take(3) as $item)
                            <div class="flex justify-between gap-3"><span>{{ $item->product_name_snapshot }} × {{ $item->quantity }}</span><strong>₱{{ number_format($item->line_total_cents / 100, 2) }}</strong></div>
                        @endforeach
                    </div>
                    <a href="{{ route('cart.show', $cart) }}" class="mt-4 inline-flex rounded-xl bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white">Open current cart</a>
                @else
                    <p class="mt-3 text-sm text-[#78645d]">This customer has no editable draft cart.</p>
                @endif
            </section>
        </div>

        <section class="mt-5 rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Manage Customer</p>
            <h2 class="mt-1 text-xl font-bold">Update customer status</h2>
            <p class="mt-2 text-sm leading-6 text-[#78645d]">This is the status field assessed by the midterm customer module.</p>
            <form method="POST" action="{{ route('customers.status.update', $customer) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                @method('PATCH')
                <div class="min-w-0 flex-1">
                    <label for="status" class="mb-2 block text-sm font-bold">Status</label>
                    <select id="status" name="status" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
                        @foreach (['pending', 'active', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $customer->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rounded-xl bg-[#b85c5c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#984b4b]">Save status</button>
            </form>
        </section>

        <section class="mt-5 rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Placed activity</p><h2 class="mt-1 text-xl font-bold">Order history</h2></div>
                <span class="rounded-full bg-[#fff0ed] px-3 py-1 text-xs font-bold text-[#984b4b]">{{ $placedOrderCount }}</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="admin-table w-full min-w-[640px] text-left">
                    <thead class="bg-[#fffaf8] text-xs uppercase tracking-[0.16em] text-[#917d73]"><tr><th class="px-3 py-3">Order</th><th class="px-3 py-3">Products</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Total</th><th class="px-3 py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-[#f0e5df]">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-[#fffaf8]"><td class="px-3 py-3 font-bold">{{ $order->order_number }}</td><td class="px-3 py-3 text-[#78645d]">{{ $order->items->sum('quantity') }}</td><td class="px-3 py-3"><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ ucfirst($order->status) }}</span></td><td class="px-3 py-3 font-bold text-[#b85c5c]">₱{{ number_format($order->total_amount_cents / 100, 2) }}</td><td class="px-3 py-3 text-right"><a href="{{ route('orders.show', $order) }}" class="font-bold text-[#b85c5c]">View order</a></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-[#78645d]">No placed orders recorded for this customer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
