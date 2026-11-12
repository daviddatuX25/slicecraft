@extends('finals.layouts.storefront')

@section('title', 'My Orders & Tracking')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">My Orders</h1>
            <p class="text-sm text-slate-500">Track current cake orders and view historical purchase receipts</p>
        </div>
        <a href="{{ route('finals.storefront') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-store"></i> Shop More
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 p-8 space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800">No orders placed yet</h2>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">When you place an order, you can monitor its baking and delivery status right here.</p>
            <a href="{{ route('finals.storefront') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-md transition-colors">
                <i class="fa-solid fa-store"></i> Browse Catalog
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <span class="font-extrabold text-slate-900 text-base">Order #{{ $order->order_number }}</span>
                            @php
                                $badgeStyles = match($order->status) {
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'processing' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeStyles }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Placed on {{ $order->created_at->format('M d, Y • h:i A') }} • {{ $order->items->sum('quantity') }} items
                        </p>
                        <p class="text-xs text-slate-600 font-medium line-clamp-1">
                            Items: {{ $order->items->pluck('product_name_snapshot')->join(', ') }}
                        </p>
                    </div>

                    <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                        <div class="text-right">
                            <span class="text-lg font-extrabold text-rose-600">₱{{ number_format($order->total_amount_cents / 100, 2) }}</span>
                        </div>
                        <a href="{{ route('finals.orders.show', $order->id) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1.5">
                            <span>Track & Details</span>
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
