<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE (change NOT in the worksheet instructions): These four stable
        // cake records provide the Product rubric data and the Page 3 demo.
        $products = [
            [
                'name' => 'Classic Ube',
                'slug' => 'classic-ube',
                'description' => 'Soft ube cake with a smooth cream finish and a gentle Filipino-inspired sweetness.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 99000,
                'image_path' => 'images/cakes/classic-ube-minimal.png',
                'is_active' => true,
            ],
            [
                'name' => 'Chocolate Fudge',
                'slug' => 'chocolate-fudge',
                'description' => 'Rich chocolate layers made for chocolate lovers and celebration tables.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 95000,
                'image_path' => 'images/cakes/chocolate-fudge-minimal.png',
                'is_active' => true,
            ],
            [
                'name' => 'Red Velvet',
                'slug' => 'red-velvet',
                'description' => 'Deep red velvet layers paired with a smooth cream-cheese-style finish.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 98000,
                'image_path' => 'images/cakes/red-velvet-minimal.png',
                'is_active' => true,
            ],
            [
                'name' => 'Mango Celebration',
                'slug' => 'mango-celebration',
                'description' => 'A bright mango-forward cake with a light cream finish for sunny celebrations.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 102000,
                'image_path' => 'images/cakes/mango-cake-minimal.png',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['slug' => $product['slug']], $product);
        }
    }
}
