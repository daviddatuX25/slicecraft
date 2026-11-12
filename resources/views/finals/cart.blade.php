@extends('finals.layouts.storefront')

@section('title', 'Your Shopping Cart')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Shopping Cart</h1>
            <p class="text-sm text-slate-500">Review selected cakes and adjust quantities before placing order</p>
        </div>
        <a href="{{ route('finals.storefront') }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Continue Shopping
        </a>
    </div>

    @if(empty($cartDetails['items']))
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 p-8 space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-3xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800">Your cart is empty</h2>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">Looks like you haven't added any sweet treats yet. Explore our handcrafted selection!</p>
            <a href="{{ route('finals.storefront') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-md transition-colors">
                <i class="fa-solid fa-store"></i> Browse Catalog
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            {{-- Cart Items List (2 cols) --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-sm">
                    @foreach($cartDetails['items'] as $item)
                        <div class="p-4 sm:p-5 flex items-center gap-4 hover:bg-slate-50/50 transition-colors" id="cart-item-row-{{ $item['id'] }}">
                            <div class="w-20 h-20 rounded-xl bg-slate-100 shrink-0 overflow-hidden flex items-center justify-center border border-slate-200">
                                @if(!empty($item['image_path']) && file_exists(public_path($item['image_path'])))
                                    <img src="{{ asset($item['image_path']) }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-cake-candles text-rose-400 text-2xl"></i>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-slate-900 text-base truncate">{{ $item['product_name'] }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Unit Price: ₱{{ $item['unit_price_pesos'] }}</p>

                                {{-- Quantity & Remove Row --}}
                                <div class="flex items-center gap-4 mt-3">
                                    <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-xs">
                                        <button
                                            type="button"
                                            onclick="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors"
                                        >
                                            <i class="fa-solid fa-minus text-xs"></i>
                                        </button>
                                        <span class="w-10 text-center font-bold text-sm text-slate-800">{{ $item['quantity'] }}</span>
                                        <button
                                            type="button"
                                            onclick="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                            class="w-8 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors"
                                        >
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        onclick="removeCartItem('{{ $item['id'] }}')"
                                        class="text-xs font-semibold text-rose-500 hover:text-rose-700 flex items-center gap-1 transition-colors"
                                    >
                                        <i class="fa-regular fa-trash-can"></i> Remove
                                    </button>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-base font-extrabold text-slate-900">₱{{ $item['line_total_pesos'] }}</div>
                                <span class="text-[10px] text-slate-400 font-medium">Subtotal</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 px-2">
                    <form action="#" onsubmit="event.preventDefault(); clearCart();">
                        <button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold underline">
                            Clear Entire Cart
                        </button>
                    </form>
                    <span>All prices are in Philippine Peso (PHP)</span>
                </div>
            </div>

            {{-- Checkout Summary Card (1 col) --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
                <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">Order Summary</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Count</span>
                        <span class="font-semibold text-slate-800">{{ $cartDetails['item_count'] }} items</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-800">₱{{ $cartDetails['total_pesos'] }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Fee</span>
                        <span class="font-semibold text-emerald-600 uppercase text-xs">FREE</span>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex justify-between text-base font-extrabold text-slate-900">
                        <span>Total Amount</span>
                        <span class="text-xl text-rose-600">₱{{ $cartDetails['total_pesos'] }}</span>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    @if(auth('customer')->check())
                        <a href="{{ route('finals.checkout') }}" class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-600/20 text-center block transition-all">
                            Proceed to Checkout <i class="fa-solid fa-arrow-right ml-1.5"></i>
                        </a>
                    @else
                        <a href="{{ route('finals.login') }}" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm rounded-xl text-center block transition-colors">
                            <i class="fa-solid fa-lock mr-1.5"></i> Login to Checkout
                        </a>
                        <p class="text-[11px] text-center text-slate-400">You will be redirected back to complete your order.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    async function updateCartItem(itemId, quantity) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch(`/finals/cart/items/${itemId}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ quantity })
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function removeCartItem(itemId) {
        if (!confirm('Remove this cake from your cart?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch(`/finals/cart/items/${itemId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function clearCart() {
        if (!confirm('Are you sure you want to clear your cart?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch(`/finals/cart`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>
@endsection
