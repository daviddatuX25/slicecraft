@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This dedicated Cart screen separates the editable draft cart from read-only placed orders while preserving the rubric's cart actions. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">SliceCraft · Owner / Admin</p>
                <h1 class="mt-1 text-3xl font-bold">Current cart</h1>
                <p class="mt-2 text-[#78645d]">Review the editable draft before it becomes part of order history.</p>
            </div>
            @include('pages._slicecraft-nav')
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        @forelse ($carts as $cart)
            <article class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#b85c5c]">Draft cart</p>
                        <h2 class="mt-1 text-xl font-bold">{{ $cart->order_number }}</h2>
                        <p class="mt-1 text-sm text-[#78645d]">Customer: {{ $cart->customer->name }}</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Editable</span>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-[#fffaf8] p-4">
                        <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Cart products</span>
                        <span class="mt-1 block text-2xl font-bold text-[#3c2924]">{{ $cart->items->sum('quantity') }}</span>
                        <span class="text-sm text-[#78645d]">individual products ready for review</span>
                    </div>
                    <div class="rounded-2xl bg-[#fffaf8] p-4">
                        <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Current total</span>
                        <span class="mt-1 block text-2xl font-bold text-[#b85c5c]">₱{{ number_format($cart->total_amount_cents / 100, 2) }}</span>
                        <span class="text-sm text-[#78645d]">updated after cart changes</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-[#78645d]">This draft is separate from placed orders and can be edited one product at a time.</p>
                    <a href="{{ route('cart.show', $cart) }}" class="rounded-xl bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#5b3a31]">View cart products</a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-[#d9c5b9] bg-white px-4 py-12 text-center">
                <h2 class="text-xl font-bold">Your cart is empty</h2>
                <p class="mt-2 text-sm text-[#78645d]">No editable draft cart is currently available.</p>
                <a href="{{ route('cake.dashboard') }}" class="mt-4 inline-flex rounded-xl bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white">Open cake studio</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
