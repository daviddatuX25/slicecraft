<?php

namespace App\Http\Controllers;

use App\Models\CakePreview;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class CakeShopController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): Page 3 is the
    // preview-first cake-shop entry point inside the existing app shell.
    public function index(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('cakePreviews')
            ->orderBy('id')
            ->get();

        $builderProducts = $products->map(function (Product $product): array {
            $fallbackImage = $product->image_path ?: 'img/p1.jpg';

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'base_price_cents' => $product->base_price_cents,
                'image_url' => asset($fallbackImage),
                'previews' => $product->cakePreviews
                    ->mapWithKeys(function (CakePreview $preview): array {
                        return [
                            $preview->selection_key => [
                                'label' => $preview->alt_text,
                                'image_url' => $preview->hasAvailableImage() ? asset($preview->image_path) : null,
                            ],
                        ];
                    })
                    ->all(),
            ];
        })->values()->all();

        return view('pages.page3', [
            'builderProducts' => $builderProducts,
            'options' => config('cake.options'),
        ]);
    }
}
