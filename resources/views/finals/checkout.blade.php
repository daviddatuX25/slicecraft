@extends('finals.layouts.storefront')

@section('title', 'Checkout & Order Review')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Checkout & Order Placement</h1>
        <p class="text-sm text-slate-500">Confirm delivery details, schedule date, and place your order</p>
    </div>

    {{-- Freshness & Guarantee Banner --}}
    <div class="rounded-2xl bg-rose-50 border border-rose-100 p-4.5 text-xs text-rose-950 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-shield-heart text-base"></i>
        </div>
        <div>
            <span class="font-bold text-slate-900 block">Freshness & Order Guarantee</span>
            <p class="text-slate-600 mt-0.5">Every cake is handcrafted and baked to order for your requested schedule. You can track real-time preparation in your orders dashboard.</p>
        </div>
    </div>

    <form id="checkout-form" onsubmit="handleCheckout(event)" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        @csrf

        {{-- Left 2 Columns: Customer & Delivery Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-circle text-rose-600"></i> Customer & Contact Information
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                        <input
                            type="text"
                            value="{{ $customer->name }}"
                            disabled
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 font-medium"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address</label>
                        <input
                            type="email"
                            value="{{ $customer->email }}"
                            disabled
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 font-medium"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Phone</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ $customer->phone }}"
                            placeholder="0917-xxx-xxxx"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery / Pickup Date</label>
                        <input
                            type="datetime-local"
                            name="scheduled_at"
                            value="{{ now()->addDays(2)->format('Y-m-d\TH:i') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Address</label>
                    <textarea
                        name="address"
                        rows="2"
                        placeholder="Complete Street, Barangay, City / Municipality, Province"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                    >{{ $customer->address }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Custom Cake Dedication / Message</label>
                    <input
                        type="text"
                        name="notes"
                        placeholder="e.g. Happy 21st Birthday Sarah! / Handle with extra care"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                    >
                </div>
            </div>
        </div>

        {{-- Right 1 Column: Order Items & Submit --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Final Order Review</h2>

            <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1">
                @foreach($cartDetails['items'] as $item)
                    <div class="py-2.5 flex justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-800">{{ $item['quantity'] }}x</span>
                            <span class="text-slate-700 ml-1 font-medium">{{ $item['product_name'] }}</span>
                        </div>
                        <span class="font-bold text-slate-900">₱{{ $item['line_total_pesos'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600 text-xs">
                    <span>Total Quantity</span>
                    <span class="font-bold text-slate-800">{{ $cartDetails['item_count'] }} items</span>
                </div>
                <div class="flex justify-between text-slate-600 text-xs">
                    <span>Delivery Fee</span>
                    <span class="font-bold text-emerald-600">FREE</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-100">
                    <span>Grand Total</span>
                    <span class="text-rose-600 text-xl">₱{{ $cartDetails['total_pesos'] }}</span>
                </div>
            </div>

            <button
                type="submit"
                id="place-order-btn"
                class="w-full py-4 px-4 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/30 text-center block transition-all"
            >
                <i class="fa-solid fa-check-circle mr-1.5"></i> Confirm & Place Order
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    async function handleCheckout(event) {
        event.preventDefault();
        const btn = document.getElementById('place-order-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Submitting & Queuing...';

        const form = document.getElementById('checkout-form');
        const formData = new FormData(form);
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const payload = {
            phone: formData.get('phone'),
            address: formData.get('address'),
            scheduled_at: formData.get('scheduled_at'),
            notes: formData.get('notes'),
        };

        try {
            const res = await fetch('/api/v1/customer/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                alert('Order Placed Successfully!\nThank you! Your order has been confirmed and our bakers are preparing it.\nOrder #: ' + data.data.order_number);
                window.location.href = "{{ route('finals.orders') }}";
            } else {
                alert('Order failed: ' + (data.message || 'Please check your information.'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check-circle mr-1.5"></i> Confirm & Place Order';
            }
        } catch (e) {
            console.error(e);
            alert('Unable to place order at this time. Please check your connection and try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check-circle mr-1.5"></i> Confirm & Place Order';
        }
    }
</script>
@endsection
