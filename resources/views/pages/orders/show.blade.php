@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): Placed order details are intentionally read-only; the exam's delete action is owned by the dedicated Cart screen. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('orders.index') }}" class="text-sm font-bold text-[#b85c5c]">Back to orders</a>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Placed order · {{ $order->order_number }}</p>
                <h1 class="mt-1 text-3xl font-bold">Order details</h1>
            </div>
            <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">{{ ucfirst($order->status) }}</span>
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="space-y-4">
                @forelse ($order->items as $item)
                    <article class="flex flex-col gap-4 rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm sm:flex-row sm:items-center">
                        <img src="{{ asset($item->product?->image_path && is_file(public_path($item->product->image_path)) ? $item->product->image_path : config('cake.unavailable_image')) }}" alt="{{ $item->product_name_snapshot }}" class="h-24 w-full rounded-2xl object-cover sm:w-32">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#b85c5c]">Quantity {{ $item->quantity }}</p>
                            <h2 class="mt-1 text-lg font-bold">{{ $item->product_name_snapshot }}</h2>
                            <p class="mt-2 text-sm leading-5 text-[#78645d]">{{ $item->customization_summary ?: 'Standard cake configuration' }}</p>
                            <p class="mt-2 font-bold text-[#b85c5c]">₱{{ number_format($item->line_total_cents / 100, 2) }}</p>
                        </div>
                        <span class="rounded-xl bg-stone-100 px-3 py-2 text-xs font-bold text-stone-700">Read-only order</span>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-[#d9c5b9] bg-white px-4 py-10 text-center text-[#78645d]">This order has no products.</div>
                @endforelse
            </section>

            <aside class="h-fit rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm lg:sticky lg:top-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Order summary</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-[#78645d]">Customer</dt><dd class="font-bold">{{ $order->customer->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-[#78645d]">Items</dt><dd class="font-bold">{{ $order->items->count() }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-[#eaded6] pt-3"><dt class="font-bold">Total</dt><dd class="text-lg font-bold text-[#b85c5c]">₱{{ number_format($order->total_amount_cents / 100, 2) }}</dd></div>
                </dl>
                <p class="mt-4 rounded-2xl bg-[#fffaf8] p-3 text-xs leading-5 text-[#78645d]">This placed order preserves the products and configuration recorded when it was submitted.</p>
            </aside>
        </div>
    </div>
</div>
@endsection
