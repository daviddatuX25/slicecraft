<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): This controller handles
    // the cake Product CRUD and the optional image uploads.
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('pages.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('pages.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:80'],
            'base_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ] + $this->imageUploadRules());

        $slug = $this->uniqueSlug($validated['name']);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'base_price_cents' => $validated['base_price'] * 100,
            'image_path' => $request->hasFile('base_image')
                ? $this->storePublicImage($request->file('base_image'), $slug, 'base')
                : config('cake.unavailable_image'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncPreviewImages($product, $request, $slug);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function show(Product $product): View
    {
        $product->load('cakePreviews');

        return view('pages.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('cakePreviews');

        return view('pages.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:80'],
            'base_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ] + $this->imageUploadRules());

        $slug = $this->uniqueSlug($validated['name'], $product->id);
        $attributes = [
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'base_price_cents' => $validated['base_price'] * 100,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('base_image')) {
            $attributes['image_path'] = $this->storePublicImage($request->file('base_image'), $slug, 'base');
            $this->deleteStoredImage($product->image_path);
        }

        $product->update($attributes);
        $this->syncPreviewImages($product, $request, $slug);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->load('cakePreviews');
        $this->deleteStoredImage($product->image_path);

        foreach ($product->cakePreviews as $preview) {
            $this->deleteStoredImage($preview->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function imageUploadRules(): array
    {
        $rules = [
            'base_image' => ['nullable', 'image', 'max:5120'],
            'preview_images' => ['nullable', 'array'],
        ];

        foreach (array_keys(config('cake.combinations')) as $key) {
            $rules['preview_images.'.$key] = ['nullable', 'image', 'max:5120'];
        }

        return $rules;
    }

    private function syncPreviewImages(Product $product, Request $request, string $productSlug): void
    {
        foreach ($request->file('preview_images', []) as $key => $file) {
            if (! array_key_exists($key, config('cake.combinations')) || ! $file instanceof UploadedFile) {
                continue;
            }

            $label = config('cake.combinations.'.$key);
            $preview = $product->cakePreviews()->firstOrNew(['selection_key' => $key]);

            if (! $preview->exists) {
                $preview->image_path = 'images/cakes/'.$productSlug.'-'.$key.'.png';
                $preview->alt_text = $product->name.' with '.$label;
            }

            $this->deleteStoredImage($preview->image_path);
            $preview->image_path = $this->storePublicImage($file, $productSlug, $key);
            $preview->save();
        }
    }

    private function storePublicImage(UploadedFile $file, string $productSlug, string $name): string
    {
        $path = $file->storeAs(
            'cakes/products/'.$productSlug,
            $name.'.'.$file->extension(),
            'public',
        );

        return 'storage/'.$path;
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path !== null && Str::startsWith($path, 'storage/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
