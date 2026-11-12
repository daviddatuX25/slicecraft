<?php

namespace App\Http\Controllers\Api\Finals;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Display a listing of products with dynamic search and category filtering.
     * Uses Eloquent ORM queries as required by Rubric 2.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $query = Product::query()
            ->where('is_active', true)
            ->with('cakePreviews');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->get();

        $formatted = $products->map(function (Product $product): array {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'category' => $product->category,
                'base_price_cents' => (int) $product->base_price_cents,
                'base_price_pesos' => number_format($product->base_price_cents / 100, 2),
                'image_path' => $product->image_path,
                'preview_count' => $product->cakePreviews->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'query' => [
                'search' => $search,
                'category' => $category,
            ],
            'count' => $formatted->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Display the specified product and its details.
     */
    public function show(int $id): JsonResponse
    {
        /** @var Product $product */
        $product = Product::query()
            ->where('is_active', true)
            ->with('cakePreviews')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'category' => $product->category,
                'base_price_cents' => (int) $product->base_price_cents,
                'base_price_pesos' => number_format($product->base_price_cents / 100, 2),
                'image_path' => $product->image_path,
                'previews' => $product->cakePreviews->map(fn ($preview) => [
                    'id' => $preview->id,
                    'preview_path' => $preview->preview_path,
                    'size' => $preview->size,
                    'tier_count' => $preview->tier_count,
                    'price_adjustment_cents' => (int) $preview->price_adjustment_cents,
                ]),
            ],
        ]);
    }
}
