@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This Product form adopts the compact Page 3 scale without changing the required update-product workflow. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-4xl">
        <header class="mb-4 border-b border-[#eaded6] pb-4">
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-sm font-bold text-[#5b3a31] hover:border-[#b85c5c]"><i class="fa fa-arrow-left mr-1" aria-hidden="true"></i> Back to product</a>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Manage Product</p>
            <h1 class="mt-1 text-3xl font-bold">Edit {{ $product->name }}</h1>
            <p class="mt-2 text-[#78645d]">Update the saved product details and availability.</p>
        </header>
        <div class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm sm:p-5">
            @include('pages.products._form', ['product' => $product])
        </div>
    </div>
</div>
@endsection
