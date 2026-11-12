{{-- NOTE (change NOT in the worksheet instructions): This form keeps the Product CRUD fields and adds optional image uploads for the preview slots. --}}
@php
    $isEditing = isset($product);
    $combinationLabels = config('cake.combinations');
    $existingPreviews = $isEditing ? $product->cakePreviews->keyBy('selection_key') : collect();
@endphp

<form method="POST" action="{{ $isEditing ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="mb-2 block text-sm font-bold">Product name</label>
            <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="120" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
            @error('name')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="category" class="mb-2 block text-sm font-bold">Category</label>
            <input id="category" name="category" value="{{ old('category', $product->category ?? 'Classic Cakes') }}" required maxlength="80" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
            @error('category')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="base_price" class="mb-2 block text-sm font-bold">Base price (PHP)</label>
            <input id="base_price" name="base_price" type="number" min="0" max="100000" step="1" value="{{ old('base_price', isset($product) ? intdiv($product->base_price_cents, 100) : '') }}" required class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
            @error('base_price')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="description" class="mb-2 block text-sm font-bold">Description</label>
            <textarea id="description" name="description" rows="4" maxlength="2000" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <section class="rounded-2xl border border-[#eaded6] bg-[#fffaf8] p-4">
        <div>
            <h2 class="text-base font-bold">Product images</h2>
            <p class="mt-1 text-sm leading-5 text-[#78645d]">Upload a base image and, when available, one image for each visual option. Leaving a slot blank keeps the unavailable artwork.</p>
        </div>
        <div class="mt-4">
            <label for="base_image" class="mb-2 block text-sm font-bold">Base product image</label>
            <input id="base_image" name="base_image" type="file" accept="image/*" class="block w-full rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#fff0ed] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#984b4b]">
            @if ($isEditing && $product->image_path)
                <p class="mt-2 truncate text-xs text-[#917d73]">Current: {{ $product->image_path }}</p>
            @endif
            @error('base_image')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($combinationLabels as $key => $label)
                @php($currentPreview = $existingPreviews->get($key))
                <div>
                    <label for="preview-image-{{ $key }}" class="mb-2 block text-sm font-bold">{{ $label }}</label>
                    <input id="preview-image-{{ $key }}" name="preview_images[{{ $key }}]" type="file" accept="image/*" class="block w-full rounded-xl border border-[#d9c5b9] bg-white px-3 py-2 text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-[#fff0ed] file:px-2.5 file:py-1.5 file:text-xs file:font-bold file:text-[#984b4b]">
                    @if ($currentPreview)
                        <p class="mt-2 truncate text-xs text-[#917d73]">Current: {{ $currentPreview->image_path }}</p>
                    @endif
                    @error('preview_images.'.$key)<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </section>

    <label class="flex items-center gap-3 text-sm font-semibold">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-[#d9c5b9] text-[#b85c5c] focus:ring-[#b85c5c]">
        Available in the product workspace
    </label>

    <div class="flex flex-wrap gap-3 border-t border-[#eaded6] pt-5">
        <button type="submit" class="rounded-xl bg-[#b85c5c] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#984b4b]">{{ $isEditing ? 'Save changes' : 'Add product' }}</button>
        <a href="{{ $isEditing ? route('products.show', $product) : route('products.index') }}" class="rounded-xl border border-[#d9c5b9] px-4 py-2.5 text-sm font-bold text-[#5b3a31] transition hover:border-[#b85c5c]">Cancel</a>
    </div>
</form>
