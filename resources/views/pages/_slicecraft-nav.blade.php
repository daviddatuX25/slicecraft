{{-- NOTE (change NOT in the worksheet instructions): This shared navigation keeps the bounded Page 3 owner/admin modules in one consistent order while preserving the midterm's Product, Customer, and Cart requirements. --}}
@php
    $navItems = [
        ['route' => 'products.index', 'label' => 'Products', 'icon' => 'fa-cubes', 'active' => request()->routeIs('cake.dashboard', 'products.*')],
        ['route' => 'customers.index', 'label' => 'Customers', 'icon' => 'fa-users', 'active' => request()->routeIs('customers.*')],
        ['route' => 'cart.index', 'label' => 'Current Cart', 'icon' => 'fa-shopping-basket', 'active' => request()->routeIs('cart.*')],
        ['route' => 'orders.index', 'label' => 'Placed Orders', 'icon' => 'fa-list-alt', 'active' => request()->routeIs('orders.*')],
    ];
@endphp

<nav class="flex flex-wrap gap-2 text-sm font-semibold" aria-label="SliceCraft owner navigation">
    @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" class="rounded-xl border px-3 py-2 transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2 {{ $item['active'] ? 'border-[#3c2924] bg-[#3c2924] text-white' : 'border-[#d9c5b9] bg-white text-[#5b3a31] hover:border-[#b85c5c]' }}">
            <i class="fa {{ $item['icon'] }} mr-1" aria-hidden="true"></i>{{ $item['label'] }}
        </a>
    @endforeach
</nav>
