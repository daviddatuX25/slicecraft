@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This Cart detail screen owns the rubric's individual cart-product deletion action; placed order details remain read-only. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('cart.index') }}" class="inline-flex items-center rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-sm font-bold text-[#5b3a31] hover:border-[#b85c5c]"><i class="fa fa-arrow-left mr-1" aria-hidden="true"></i> Back to current cart</a>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Editable cart · {{ $cart->order_number }}</p>
                <h1 class="mt-1 text-3xl font-bold">Cart products</h1>
            </div>
            <a href="{{ route('orders.index') }}" class="rounded-xl bg-[#3c2924] px-3 py-2 text-sm font-bold text-white"><i class="fa fa-list-alt mr-1" aria-hidden="true"></i> Placed Orders</a>
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="space-y-4">
                @forelse ($cart->items as $item)
                    <article class="flex flex-col gap-4 rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm sm:flex-row sm:items-center">
                        <img src="{{ asset($item->product?->image_path ?: 'img/p1.jpg') }}" alt="{{ $item->product_name_snapshot }}" class="h-24 w-full rounded-2xl object-cover sm:w-32">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#b85c5c]">Quantity {{ $item->quantity }}</p>
                            <h2 class="mt-1 text-lg font-bold">{{ $item->product_name_snapshot }}</h2>
                            <p class="mt-2 text-sm leading-5 text-[#78645d]">{{ $item->customization_summary ?: 'Standard cake configuration' }}</p>
                            <p class="mt-2 font-bold text-[#b85c5c]">₱{{ number_format($item->line_total_cents / 100, 2) }}</p>
                        </div>
                        <form method="POST" action="{{ route('cart-items.destroy', [$cart, $item]) }}" x-data="{ confirming: false }" class="sm:self-start">
                            @csrf
                            @method('DELETE')
                            <button type="button" x-show="!confirming" @click="confirming = true" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-bold text-red-700 hover:bg-red-100">Remove item</button>
                            <span x-show="confirming" class="flex items-center gap-2" x-cloak>
                                <button type="submit" class="rounded-xl bg-red-600 px-3 py-2 text-sm font-bold text-white hover:bg-red-700">Confirm</button>
                                <button type="button" @click="confirming = false" class="rounded-xl border border-[#d9c5b9] px-3 py-2 text-sm font-bold">Cancel</button>
                            </span>
                        </form>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-[#d9c5b9] bg-white px-4 py-10 text-center text-[#78645d]">This cart has no products.</div>
                @endforelse
            </section>

            <aside class="h-fit rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm lg:sticky lg:top-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Cart summary</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-[#78645d]">Customer</dt><dd class="font-bold">{{ $cart->customer->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-[#78645d]">Products</dt><dd class="font-bold">{{ $cart->items->count() }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-[#eaded6] pt-3"><dt class="font-bold">Total</dt><dd class="text-lg font-bold text-[#b85c5c]">₱{{ number_format($cart->total_amount_cents / 100, 2) }}</dd></div>
                </dl>
                <p class="mt-4 rounded-2xl bg-[#fffaf8] p-3 text-xs leading-5 text-[#78645d]">Remove one cart product at a time. The draft remains available and its total is recalculated after each deletion.</p>
            </aside>
        </div>
    </div>
</div>
@endsection
