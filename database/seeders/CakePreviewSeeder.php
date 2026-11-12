<?php

namespace Database\Seeders;

use App\Models\CakePreview;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CakePreviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE (change NOT in the worksheet instructions): This list adds the
        // real cake images to the products. Missing images are not added.
        $previewGroups = [
            'classic-ube' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/classic-ube-minimal.png', 'alt_text' => 'Classic Ube cake with a minimal cream finish'],
                ['selection_key' => 'rosettes', 'image_path' => 'images/cakes/classic-ube-rosettes.png', 'alt_text' => 'Classic Ube cake with whipped cream rosettes'],
                ['selection_key' => 'coconut', 'image_path' => 'images/cakes/classic-ube-coconut.png', 'alt_text' => 'Classic Ube cake with toasted coconut garnish'],
                ['selection_key' => 'fruit', 'image_path' => 'images/cakes/classic-ube-fruit.png', 'alt_text' => 'Classic Ube cake with fresh fruit topping'],
                ['selection_key' => 'celebration', 'image_path' => 'images/cakes/classic-ube-celebration.png', 'alt_text' => 'Classic Ube cake with celebration decoration'],
                ['selection_key' => 'deluxe-rosettes-coconut', 'image_path' => 'images/cakes/classic-ube-deluxe-rosettes-coconut.png', 'alt_text' => 'Classic Ube cake with rosettes and toasted coconut'],
                ['selection_key' => 'deluxe-coconut-berries', 'image_path' => 'images/cakes/classic-ube-deluxe-coconut-berries.png', 'alt_text' => 'Classic Ube cake with coconut and berries'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/classic-ube-bento.png', 'alt_text' => 'Bento-size Classic Ube cake'],
            ],
            'chocolate-fudge' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/chocolate-fudge-minimal.png', 'alt_text' => 'Chocolate Fudge cake with a minimal finish'],
                ['selection_key' => 'web-glaze', 'image_path' => 'images/cakes/chocolate-fudge-web-glaze.png', 'alt_text' => 'Chocolate Fudge cake with a feathered web glaze'],
                ['selection_key' => 'drip', 'image_path' => 'images/cakes/chocolate-fudge-drip.png', 'alt_text' => 'Chocolate Fudge cake with a chocolate drip'],
                ['selection_key' => 'berries', 'image_path' => 'images/cakes/chocolate-fudge-berries.png', 'alt_text' => 'Chocolate Fudge cake with fresh berries'],
                ['selection_key' => 'macarons', 'image_path' => 'images/cakes/chocolate-fudge-macarons.png', 'alt_text' => 'Chocolate Fudge cake with macarons'],
                ['selection_key' => 'deluxe-drip-macarons', 'image_path' => 'images/cakes/chocolate-fudge-deluxe-drip-macarons.png', 'alt_text' => 'Chocolate Fudge cake with drip and macarons'],
                ['selection_key' => 'deluxe-drip-berries', 'image_path' => 'images/cakes/chocolate-fudge-deluxe-drip-berries.png', 'alt_text' => 'Chocolate Fudge cake with drip and berries'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/chocolate-fudge-bento.png', 'alt_text' => 'Bento-size Chocolate Fudge cake'],
            ],
            'red-velvet' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/red-velvet-minimal.png', 'alt_text' => 'Red Velvet cake with a minimal cream-cheese finish'],
                ['selection_key' => 'berries', 'image_path' => 'images/cakes/red-velvet-berries.png', 'alt_text' => 'Red Velvet cake with fresh berries'],
                ['selection_key' => 'drip', 'image_path' => 'images/cakes/red-velvet-drip.png', 'alt_text' => 'Red Velvet cake with a chocolate drip'],
                ['selection_key' => 'deluxe-berries-macarons', 'image_path' => 'images/cakes/red-velvet-deluxe-berries-macarons.png', 'alt_text' => 'Red Velvet cake with berries and macarons'],
                ['selection_key' => 'celebration', 'image_path' => 'images/cakes/red-velvet-celebration.png', 'alt_text' => 'Red Velvet cake with celebration decoration'],
                ['selection_key' => 'deluxe-drip-macarons', 'image_path' => 'images/cakes/red-velvet-deluxe-drip-macarons.png', 'alt_text' => 'Red Velvet cake with drip and macarons'],
                ['selection_key' => 'deluxe-drip-berries', 'image_path' => 'images/cakes/red-velvet-deluxe-drip-berries.png', 'alt_text' => 'Red Velvet cake with drip and berries'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/red-velvet-bento.png', 'alt_text' => 'Bento-size Red Velvet cake'],
            ],
            'mango-celebration' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/mango-cake-minimal.png', 'alt_text' => 'Mango Celebration cake with a minimal finish'],
                ['selection_key' => 'tropical-fruit', 'image_path' => 'images/cakes/mango-cake-tropical-fruit.png', 'alt_text' => 'Mango Celebration cake with tropical fruit'],
                ['selection_key' => 'meringue-symphony', 'image_path' => 'images/cakes/mango-cake-meringue-symphony.png', 'alt_text' => 'Mango Celebration cake with meringue decoration'],
                ['selection_key' => 'coconut', 'image_path' => 'images/cakes/mango-cake-coconut.png', 'alt_text' => 'Mango Celebration cake with toasted coconut'],
                ['selection_key' => 'celebration', 'image_path' => 'images/cakes/mango-cake-celebration.png', 'alt_text' => 'Mango Celebration cake with celebration decoration'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/mango-cake-bento.png', 'alt_text' => 'Bento-size Mango Celebration cake'],
            ],
        ];

        foreach ($previewGroups as $slug => $previews) {
            $product = Product::where('slug', $slug)->firstOrFail();
            $selectionKeys = array_column($previews, 'selection_key');

            if ($selectionKeys === []) {
                $product->cakePreviews()->delete();
            } else {
                $product->cakePreviews()->whereNotIn('selection_key', $selectionKeys)->delete();
            }

            foreach ($previews as $preview) {
                CakePreview::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'selection_key' => $preview['selection_key'],
                    ],
                    [
                        'image_path' => $preview['image_path'],
                        'alt_text' => $preview['alt_text'],
                    ],
                );
            }
        }
    }
}
