<?php

namespace App\Http\Controllers;

use App\Models\CakePreview;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class CakeShopController extends Controller
{
    // NOTE (change NOT in the worksheet instructions): Page 3 shows the cake
    // products and their preview images in the existing app shell.
    public function index(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('cakePreviews')
            ->orderBy('id')
            ->get();
        $customers = Customer::query()->orderBy('name')->get(['id', 'name', 'email']);

        $builderProducts = $products->map(function (Product $product): array {
            $baseImageAvailable = $product->image_path && is_file(public_path($product->image_path));

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'base_price_cents' => $product->base_price_cents,
                'image_url' => asset($baseImageAvailable ? $product->image_path : config('cake.unavailable_image')),
                'base_image_available' => $baseImageAvailable,
                'previews' => $product->cakePreviews
                    ->mapWithKeys(function (CakePreview $preview): array {
                        return [
                            $preview->selection_key => [
                                'label' => $preview->alt_text,
                                'image_url' => asset($preview->hasAvailableImage() ? $preview->image_path : config('cake.unavailable_image')),
                                'image_available' => $preview->hasAvailableImage(),
                            ],
                        ];
                    })
                    ->all(),
            ];
        })->values()->all();

        return view('pages.page3', [
            'builderProducts' => $builderProducts,
            'options' => config('cake.options'),
            'customers' => $customers,
        ]);
    }
}
