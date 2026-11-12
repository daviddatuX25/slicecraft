<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Online Shopping Storefront') · SliceCraft Customer View</title>

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Tailwind CSS & Scripts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex flex-col min-h-full text-slate-800 antialiased">

    {{-- Top Teacher & Reviewer Announcement Banner --}}
    <div class="bg-gradient-to-r from-rose-700 via-rose-600 to-amber-600 text-white text-xs py-1.5 px-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="bg-white/20 px-2 py-0.5 rounded font-bold uppercase tracking-wider text-[10px]">Final Exam Upgrade</span>
                <span class="hidden sm:inline">Prof Elective 5 — Advanced Web Development (Back-End) • ISPSC CAS BSIT</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('cake.dashboard') }}" class="font-semibold text-rose-100 hover:text-white underline decoration-rose-300 flex items-center gap-1">
                    <i class="fa fa-arrow-left"></i> Switch to Midterm Admin View (/p3)
                </a>
            </div>
        </div>
    </div>

    {{-- Main Customer Header --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">

                {{-- Brand Logo --}}
                <a href="{{ route('finals.storefront') }}" class="flex items-center gap-3 shrink-0 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center text-white shadow-md shadow-rose-500/20 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-cake-candles text-xl"></i>
                    </div>
                    <div>
                        <div class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-rose-600 transition-colors">
                            SliceCraft <span class="text-rose-600">Store</span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400">Customer View • Final Exam</p>
                    </div>
                </a>

                {{-- Global Search Bar --}}
                <div class="flex-1 max-w-lg hidden md:block">
                    <form action="{{ route('finals.storefront') }}" method="GET" class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search handcrafted cakes, flavors, descriptions..."
                            class="w-full pl-11 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all placeholder:text-slate-400"
                        >
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            Search
                        </button>
                    </form>
                </div>

                {{-- Customer Navigation / Account / Cart --}}
                <div class="flex items-center gap-3">
                    {{-- Catalog Link --}}
                    <a href="{{ route('finals.storefront') }}" class="px-3 py-2 text-sm font-semibold {{ request()->routeIs('finals.storefront') ? 'text-rose-600 bg-rose-50 rounded-xl' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="fa-solid fa-store mr-1.5"></i> Catalog
                    </a>

                    {{-- Cart Button with Badge --}}
                    @php
                        $activeCart = app(\App\Services\Finals\CustomerCartService::class)->getCartDetails(auth('customer')->user());
                        $cartCount = $activeCart['item_count'] ?? 0;
                    @endphp
                    <a href="{{ route('finals.cart') }}" class="relative px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 font-semibold text-sm text-slate-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-cart-shopping text-base text-rose-600"></i>
                        <span class="hidden sm:inline">Cart</span>
                        <span id="header-cart-badge" class="px-2 py-0.5 rounded-full text-xs font-bold {{ $cartCount > 0 ? 'bg-rose-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                            {{ $cartCount }}
                        </span>
                    </a>

                    {{-- Customer Auth Status --}}
                    @if(auth('customer')->check())
                        <div class="relative group">
                            <button class="flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-sm font-semibold text-slate-700">
                                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth('customer')->user()->name, 0, 1)) }}
                                </div>
                                <span class="max-w-[120px] truncate hidden sm:inline">{{ auth('customer')->user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl py-1.5 hidden group-hover:block z-50">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-xs font-semibold text-slate-800">{{ auth('customer')->user()->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ auth('customer')->user()->email }}</p>
                                </div>
                                <a href="{{ route('finals.orders') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-rose-50 hover:text-rose-600">
                                    <i class="fa-solid fa-box text-slate-400"></i> My Orders
                                </a>
                                <form action="{{ route('finals.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('finals.login') }}" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition-colors">
                                Login
                            </a>
                            <a href="{{ route('finals.register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">
                                Register
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </header>

    {{-- Flash Notifications --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                <div class="font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg"></i>
                <div class="font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-info text-amber-500 text-lg"></i>
                <div class="font-semibold">{{ session('warning') }}</div>
            </div>
        @endif
    </div>

    {{-- Main Body --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- Storefront Footer --}}
    <footer class="bg-white border-t border-slate-200 mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">SliceCraft Online Shopping Website</span>
                <span>•</span>
                <span>Prof Elective 5 (Advanced Web Development: Back-end)</span>
            </div>
            <div>
                <span>Ilocos Sur Polytechnic State College (ISPSC) — College of Arts and Sciences (BSIT 2026-2027)</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cake.dashboard') }}" class="text-rose-600 hover:underline font-semibold">Midterm Admin (/p3)</a>
                <span>•</span>
                <a href="/api/v1/customer/products" target="_blank" class="text-slate-600 hover:text-slate-900 font-mono">REST API (/api/v1)</a>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
