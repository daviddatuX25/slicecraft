@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This order list makes the exam's Manage Cart/Order module visible inside Page 3 while keeping the existing shell. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Cake Shop · Page 3</p>
                <h1 class="mt-1 text-3xl font-bold">Order history</h1>
                <p class="mt-2 text-[#78645d]">Review placed orders and their saved cake configurations.</p>
            </div>
            @include('pages._slicecraft-nav')
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#b85c5c]">{{ $order->order_number }}</p><h2 class="mt-1 text-lg font-bold">{{ $order->customer->name }}</h2></div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ ucfirst($order->status) }}</span>
                    </div>
                    <p class="mt-3 text-sm text-[#78645d]">{{ $order->items->count() }} products · Select to view details</p>
                    <p class="mt-4 text-xl font-bold text-[#b85c5c]">₱{{ number_format($order->total_amount_cents / 100, 2) }}</p>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-[#d9c5b9] bg-white px-4 py-10 text-center md:col-span-2">No orders found.</div>
            @endforelse
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
