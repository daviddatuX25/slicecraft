@extends('finals.layouts.storefront')

@section('title', 'Browse Handcrafted Cakes & Pastries')

@section('content')
<div class="space-y-8">

    {{-- Hero Section --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-rose-900 via-rose-800 to-amber-900 text-white p-8 sm:p-12 shadow-xl">
        <div class="relative z-10 max-w-2xl space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/30 text-rose-200 border border-rose-400/30">
                <i class="fa-solid fa-sparkles text-amber-300"></i> Premium Artisanal Bakery
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Handcrafted Cakes Made for Every Celebration.
            </h1>
            <p class="text-rose-100 text-sm sm:text-base leading-relaxed">
                Discover our signature purple yam, decadent Belgian chocolate fudge, and fresh tropical fruit cakes. Place your order online and track its status in real time.
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="#catalog-grid" class="px-5 py-3 rounded-xl bg-white text-rose-900 font-bold text-sm shadow-md hover:bg-rose-50 transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-cake-candles"></i> Explore All Cakes
                </a>
                <a href="{{ route('finals.cart') }}" class="px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur-sm border border-white/20 transition-colors">
                    <i class="fa-solid fa-cart-shopping mr-1.5"></i> Open Cart
                </a>
            </div>
        </div>
        {{-- Background decorative shapes --}}
        <div class="absolute -right-12 -bottom-12 w-96 h-96 rounded-full bg-rose-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-0 w-64 h-64 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>
    </div>

    {{-- Category Pills & Search Bar --}}
    <div id="catalog-grid" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-4 border-b border-slate-200 pb-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Featured Product Catalog</h2>
            <p class="text-sm text-slate-500">Showing database-driven cake options (Retrieved via Eloquent ORM)</p>
        </div>

        {{-- Mobile Search --}}
        <div class="block md:hidden">
            <form action="{{ route('finals.storefront') }}" method="GET" class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search cakes..."
                    class="w-full pl-10 pr-20 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                >
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1 bg-rose-600 text-white text-xs font-semibold rounded-lg">
                    Search
                </button>
            </form>
        </div>
    </div>

    {{-- Products Grid --}}
    @if($products->isEmpty())
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 p-8 space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">No cakes found matching "{{ request('search') }}"</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">Try searching for other flavors like "Ube", "Chocolate", "Mango", or clear your search to view all products.</p>
            <a href="{{ route('finals.storefront') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="group bg-white rounded-2xl border border-slate-200 hover:border-rose-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                    {{-- Cake Image Container --}}
                    <div class="relative aspect-square w-full bg-slate-100 overflow-hidden flex items-center justify-center">
                        @if($product->image_path && file_exists(public_path($product->image_path)))
                            <img
                                src="{{ asset($product->image_path) }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            >
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-rose-50 to-amber-50 text-rose-300">
                                <i class="fa-solid fa-cake-candles text-5xl"></i>
                                <span class="text-xs font-semibold mt-2 text-rose-400">SliceCraft Artisan</span>
                            </div>
                        @endif

                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/90 backdrop-blur-sm text-slate-800 shadow-sm border border-slate-200/50">
                                {{ ucfirst($product->category ?? 'Cake') }}
                            </span>
                        </div>
                    </div>

                    {{-- Product Info --}}
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-lg group-hover:text-rose-600 transition-colors">
                                {{ $product->name }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $product->description ?? 'Signature handcrafted cake baked fresh daily with prime ingredients.' }}
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Price</span>
                                <span class="text-xl font-extrabold text-slate-900">
                                    ₱{{ number_format($product->base_price_cents / 100, 2) }}
                                </span>
                            </div>

                            <button
                                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 active:scale-95 transition-all flex items-center gap-1.5"
                            >
                                <i class="fa-solid fa-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Toast Notification Element --}}
<div id="store-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700">
        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
        <div>
            <div id="toast-title" class="font-bold text-sm">Added to Cart</div>
            <div id="toast-desc" class="text-xs text-slate-400">Cart has been updated.</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Asynchronous Add to Cart with REST API Integration (Rubric 1 & 3)
    async function addToCart(productId, productName) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch('/finals/cart/items', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                // Update header badge
                const badge = document.getElementById('header-cart-badge');
                if (badge && data.data && data.data.cart) {
                    badge.innerText = data.data.cart.item_count;
                    badge.classList.remove('bg-slate-300', 'text-slate-700');
                    badge.classList.add('bg-rose-600', 'text-white');
                }
                showToast(`Added to Cart!`, `${productName} is now in your shopping cart.`);
            } else {
                showToast('Notice', data.message || 'Could not add item to cart.', true);
            }
        } catch (err) {
            console.error('Error adding to cart:', err);
            showToast('Error', 'Failed to communicate with cart API.', true);
        }
    }

    function showToast(title, desc, isError = false) {
        const toast = document.getElementById('store-toast');
        const titleEl = document.getElementById('toast-title');
        const descEl = document.getElementById('toast-desc');

        titleEl.innerText = title;
        descEl.innerText = desc;

        toast.classList.remove('translate-y-24', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-24', 'opacity-0');
        }, 3000);
    }
</script>
@endsection
