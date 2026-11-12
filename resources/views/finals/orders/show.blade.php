@extends('finals.layouts.storefront')

@section('title', 'Order #' . $order->order_number . ' Details')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Order #{{ $order->order_number }}</h1>
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
            <p class="text-xs text-slate-500 mt-1">Placed on {{ $order->created_at->format('F d, Y • h:i A') }}</p>
        </div>
        <a href="{{ route('finals.orders') }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    {{-- Order Status Timeline --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Order Progress</h2>
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 font-bold border border-emerald-200">
                <i class="fa-solid fa-circle-check text-emerald-600 mb-1 block text-base"></i>
                <span>1. Order Placed</span>
            </div>
            <div class="p-3 rounded-xl {{ in_array($order->status, ['processing', 'completed']) ? 'bg-sky-50 text-sky-800 font-bold border border-sky-200' : 'bg-slate-50 text-slate-400' }}">
                <i class="fa-solid fa-fire-burner mb-1 block text-base"></i>
                <span>2. Baking & Prep</span>
            </div>
            <div class="p-3 rounded-xl {{ $order->status === 'completed' ? 'bg-emerald-50 text-emerald-800 font-bold border border-emerald-200' : 'bg-slate-50 text-slate-400' }}">
                <i class="fa-solid fa-box text-base mb-1 block"></i>
                <span>3. Completed</span>
            </div>
        </div>
    </div>

    {{-- Order Information & Items --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between gap-4 text-xs">
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Customer Details</span>
                <span class="font-bold text-slate-800 text-sm block mt-0.5">{{ $order->customer?->name }}</span>
                <span class="text-slate-500 block">{{ $order->customer?->email }}</span>
                <span class="text-slate-500 block">{{ $order->customer?->phone ?? 'No phone specified' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Delivery Schedule & Notes</span>
                <span class="font-bold text-slate-800 block mt-0.5">
                    {{ $order->scheduled_at ? $order->scheduled_at->format('M d, Y • h:i A') : 'Standard 48-Hour Pickup' }}
                </span>
                @if($order->notes)
                    <span class="text-rose-600 font-medium block mt-1">Dedication: "{{ $order->notes }}"</span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Itemized Receipt</h3>
            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-800 text-sm">{{ $item->product_name_snapshot }}</span>
                            <span class="text-slate-500 ml-2">Qty: {{ $item->quantity }}</span>
                            <div class="text-[11px] text-slate-400">Unit: ₱{{ number_format($item->unit_price_cents / 100, 2) }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-slate-900 text-sm">₱{{ number_format($item->line_total_cents / 100, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-between items-center text-sm">
                <span class="font-extrabold text-slate-800">Grand Total Amount</span>
                <span class="text-2xl font-black text-rose-600">₱{{ number_format($order->total_amount_cents / 100, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
