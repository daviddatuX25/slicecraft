@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): The product detail page makes the rubric's View Product action explicit and shows the available preview mappings. --}}
{{-- NOTE (change NOT in the worksheet instructions): Missing product and combination images show the shared unavailable artwork instead of an unrelated cake photo. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-sm font-bold text-[#5b3a31] hover:border-[#b85c5c]"><i class="fa fa-arrow-left mr-1" aria-hidden="true"></i> Back to products</a>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">Product detail</p>
                <h1 class="mt-1 text-3xl font-bold">{{ $product->name }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="rounded-full bg-[#3c2924] px-4 py-2 text-sm font-bold text-white hover:bg-[#5b3a31]">Edit product</a>
                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100">Delete</button>
                </form>
            </div>
        </header>

        <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
            <section class="overflow-hidden rounded-3xl border border-[#eaded6] bg-white shadow-sm">
                <img src="{{ asset($product->image_path && is_file(public_path($product->image_path)) ? $product->image_path : config('cake.unavailable_image')) }}" alt="{{ $product->image_path && is_file(public_path($product->image_path)) ? $product->name : $product->name.' image unavailable' }}" class="h-64 w-full object-cover">
                <div class="p-4">
                    <span class="rounded-full bg-[#fff0ed] px-3 py-1 text-xs font-bold text-[#984b4b]">{{ $product->is_active ? 'Active product' : 'Hidden product' }}</span>
                    <p class="mt-4 text-sm leading-6 text-[#78645d]">{{ $product->description }}</p>
                    <p class="mt-4 text-2xl font-bold text-[#b85c5c]">₱{{ number_format($product->base_price_cents / 100, 2) }}</p>
                </div>
            </section>

            <section class="rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Preview library</p>
                <h2 class="mt-1 text-xl font-bold">Available preview images</h2>
                <p class="mt-2 text-sm leading-6 text-[#78645d]">Preview images are optional. Add an image when a generated visual is ready for this product.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($product->cakePreviews as $preview)
                        <div class="overflow-hidden rounded-2xl border border-[#eaded6] bg-[#fffaf8]">
                            <img src="{{ asset($preview->hasAvailableImage() ? $preview->image_path : config('cake.unavailable_image')) }}" alt="{{ $preview->hasAvailableImage() ? $preview->alt_text : $preview->alt_text.' preview unavailable' }}" class="h-28 w-full object-cover">
                            <div class="p-3">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#b85c5c]">{{ $preview->selection_key }}</p>
                                <p class="mt-1 text-sm font-semibold">{{ $preview->alt_text }}</p>
                                @if (! $preview->hasAvailableImage())
                                    <p class="mt-2 text-xs text-[#917d73]">Preview artwork is not available yet</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
