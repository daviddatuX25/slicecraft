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
        // NOTE (change NOT in the worksheet instructions): The seeder registers
        // only the generated files that have passed review so the UI reflects
        // real asset progress without creating placeholder preview records.
        $previewGroups = [
            'classic-ube' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/classic-ube-minimal.png', 'alt_text' => 'Classic Ube cake with a minimal cream finish'],
                ['selection_key' => 'rosettes', 'image_path' => 'images/cakes/classic-ube-rosettes.png', 'alt_text' => 'Classic Ube cake with whipped cream rosettes'],
                ['selection_key' => 'coconut', 'image_path' => 'images/cakes/classic-ube-coconut.png', 'alt_text' => 'Classic Ube cake with toasted coconut garnish'],
                ['selection_key' => 'fruit', 'image_path' => 'images/cakes/classic-ube-fruit.png', 'alt_text' => 'Classic Ube cake with fresh fruit topping'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/classic-ube-bento.png', 'alt_text' => 'Bento-size Classic Ube cake'],
            ],
            'chocolate-fudge' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/chocolate-fudge-minimal.png', 'alt_text' => 'Chocolate Fudge cake with a minimal finish'],
                ['selection_key' => 'web-glaze', 'image_path' => 'images/cakes/chocolate-fudge-web-glaze.png', 'alt_text' => 'Chocolate Fudge cake with a feathered web glaze'],
                ['selection_key' => 'drip', 'image_path' => 'images/cakes/chocolate-fudge-drip.png', 'alt_text' => 'Chocolate Fudge cake with a chocolate drip'],
                ['selection_key' => 'berries', 'image_path' => 'images/cakes/chocolate-fudge-berries.png', 'alt_text' => 'Chocolate Fudge cake with fresh berries'],
                ['selection_key' => 'macarons', 'image_path' => 'images/cakes/chocolate-fudge-macarons.png', 'alt_text' => 'Chocolate Fudge cake with macarons'],
                ['selection_key' => 'bento', 'image_path' => 'images/cakes/chocolate-fudge-bento.png', 'alt_text' => 'Bento-size Chocolate Fudge cake'],
            ],
            'red-velvet' => [
                ['selection_key' => 'minimal', 'image_path' => 'images/cakes/red-velvet-minimal.png', 'alt_text' => 'Red Velvet cake with a minimal cream-cheese finish'],
                ['selection_key' => 'berries', 'image_path' => 'images/cakes/red-velvet-berries.png', 'alt_text' => 'Red Velvet cake with fresh berries'],
            ],
            'mango-celebration' => [],
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
