@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This Tailwind page is the polished Page 3 Product CRUD surface required by the midterm rubric. --}}
{{-- NOTE (change NOT in the worksheet instructions): Missing product images show the shared unavailable artwork instead of an unrelated cake photo. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-7xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">SliceCraft · Owner / Admin</p>
                <h1 class="mt-1 text-3xl font-bold">Products</h1>
                <p class="mt-2 text-[#78645d]">Search, view, add, update, and remove cake bases.</p>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                @include('pages._slicecraft-nav')
                <a href="{{ route('products.create') }}" class="rounded-xl bg-[#b85c5c] px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#984b4b]"><i class="fa fa-plus mr-1" aria-hidden="true"></i> Add product</a>
            </div>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('products.index') }}" class="mb-4 flex flex-col gap-3 rounded-3xl border border-[#eaded6] bg-white p-3 shadow-sm sm:flex-row">
            <label for="search" class="sr-only">Search products</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Search by product name or category" class="min-w-0 flex-1 rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
            <button type="submit" class="rounded-xl bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b3a31]">Search</button>
        </form>

        @if ($products->count())
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($products as $product)
                    <article class="overflow-hidden rounded-3xl border border-[#eaded6] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <img src="{{ asset($product->image_path && is_file(public_path($product->image_path)) ? $product->image_path : config('cake.unavailable_image')) }}" alt="{{ $product->image_path && is_file(public_path($product->image_path)) ? $product->name : $product->name.' image unavailable' }}" class="h-40 w-full object-cover">
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#b85c5c]">{{ $product->category }}</p>
                                    <h2 class="mt-1 text-lg font-bold">{{ $product->name }}</h2>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-700' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                            </div>
                            <p class="mt-2 line-clamp-3 text-sm leading-5 text-[#78645d]">{{ $product->description }}</p>
                            <p class="mt-3 text-base font-bold text-[#b85c5c]">₱{{ number_format($product->base_price_cents / 100, 2) }}</p>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('products.show', $product) }}" class="flex-1 rounded-xl border border-[#d9c5b9] px-3 py-2 text-center text-sm font-bold text-[#5b3a31] hover:border-[#b85c5c]">View</a>
                                <a href="{{ route('products.edit', $product) }}" class="flex-1 rounded-xl bg-[#fff0ed] px-3 py-2 text-center text-sm font-bold text-[#984b4b] hover:bg-[#f9ded8]">Edit</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @else
            <div class="rounded-3xl border border-dashed border-[#d9c5b9] bg-white px-6 py-14 text-center">
                <h2 class="text-xl font-bold">No products found</h2>
                <p class="mt-2 text-sm text-[#78645d]">Try another search or add the first cake product.</p>
            </div>
        @endif
    </div>
</div>
@endsection
