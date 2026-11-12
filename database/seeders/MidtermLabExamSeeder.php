<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MidtermLabExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates database products and seeds two customers:
     * - Customer 1 (Maria Santos) has 10 products.
     * - Customer 2 (Juan Dela Cruz) has 2 products.
     */
    public function run(): void
    {
        // 1. Populate database products (12 distinct cake products)
        $productsData = [
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
                'category' => 'Specialty Cakes',
                'base_price_cents' => 102000,
                'image_path' => 'images/cakes/mango-cake-minimal.png',
                'is_active' => true,
            ],
            [
                'name' => 'Strawberry Shortcake',
                'slug' => 'strawberry-shortcake',
                'description' => 'Fluffy vanilla sponge layered with fresh strawberries and whipped chantilly cream.',
                'category' => 'Specialty Cakes',
                'base_price_cents' => 105000,
                'image_path' => 'images/cakes/red-velvet-berries.png',
                'is_active' => true,
            ],
            [
                'name' => 'Carrot Walnut Cake',
                'slug' => 'carrot-walnut-cake',
                'description' => 'Moist spiced carrot sponge topped with toasted walnuts and silky cream cheese.',
                'category' => 'Signature Cakes',
                'base_price_cents' => 92000,
                'image_path' => 'images/cakes/classic-ube-coconut.png',
                'is_active' => true,
            ],
            [
                'name' => 'Matcha Green Tea Cake',
                'slug' => 'matcha-green-tea-cake',
                'description' => 'Earthy ceremonial-grade matcha chiffon with smooth white chocolate frosting.',
                'category' => 'Signature Cakes',
                'base_price_cents' => 99000,
                'image_path' => 'images/cakes/classic-ube-rosettes.png',
                'is_active' => true,
            ],
            [
                'name' => 'Black Forest Gateau',
                'slug' => 'black-forest-gateau',
                'description' => 'Layers of rich chocolate sponge, tart cherry compote, and fresh chantilly cream.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 108000,
                'image_path' => 'images/cakes/chocolate-fudge-berries.png',
                'is_active' => true,
            ],
            [
                'name' => 'Blueberry Cheesecake',
                'slug' => 'blueberry-cheesecake',
                'description' => 'Creamy baked cheesecake crowned with wild blueberry glaze and graham crust.',
                'category' => 'Specialty Cakes',
                'base_price_cents' => 115000,
                'image_path' => 'images/cakes/classic-ube-fruit.png',
                'is_active' => true,
            ],
            [
                'name' => 'Mocha Caramel Crunch',
                'slug' => 'mocha-caramel-crunch',
                'description' => 'Bold espresso-infused sponge with salted caramel drizzle and honeycomb crunch.',
                'category' => 'Signature Cakes',
                'base_price_cents' => 94000,
                'image_path' => 'images/cakes/chocolate-fudge-macarons.png',
                'is_active' => true,
            ],
            [
                'name' => 'Tiramisu Meltdown',
                'slug' => 'tiramisu-meltdown',
                'description' => 'Coffee-soaked ladyfingers enveloped in mascarpone mousse and cocoa dusting.',
                'category' => 'Signature Cakes',
                'base_price_cents' => 110000,
                'image_path' => 'images/cakes/red-velvet-drip.png',
                'is_active' => true,
            ],
            [
                'name' => 'Vanilla Bean Dream',
                'slug' => 'vanilla-bean-dream',
                'description' => 'Classic Madagascar vanilla bean chiffon with delicate buttercream frosting.',
                'category' => 'Classic Cakes',
                'base_price_cents' => 88000,
                'image_path' => 'images/cakes/mango-cake-tropical-fruit.png',
                'is_active' => true,
            ],
        ];

        $products = [];
        foreach ($productsData as $data) {
            $products[$data['slug']] = Product::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }

        // 2. Ensure customers exist
        $customer1 = Customer::updateOrCreate(
            ['email' => 'maria@slicecraft.test'],
            [
                'name' => 'Maria Santos',
                'phone' => '0917 555 0101',
                'address' => 'Vigan City',
                'status' => 'active',
            ],
        );

        $customer2 = Customer::updateOrCreate(
            ['email' => 'juan@slicecraft.test'],
            [
                'name' => 'Juan Dela Cruz',
                'phone' => '0917 555 0102',
                'address' => 'Candon City',
                'status' => 'active',
            ],
        );

        // 3. Customer 1 (Maria Santos) has 10 products
        $tenProductSlugs = [
            'classic-ube',
            'chocolate-fudge',
            'red-velvet',
            'mango-celebration',
            'strawberry-shortcake',
            'carrot-walnut-cake',
            'matcha-green-tea-cake',
            'black-forest-gateau',
            'blueberry-cheesecake',
            'mocha-caramel-crunch',
        ];

        // Seed Customer 1 draft cart with 10 products
        $cart1 = Order::updateOrCreate(
            ['order_number' => 'SC-1001'],
            [
                'customer_id' => $customer1->id,
                'status' => Order::CART_STATUS,
                'total_amount_cents' => 0,
                'notes' => 'Draft cart with 10 products for Maria Santos.',
                'scheduled_at' => null,
            ],
        );

        $cart1->items()->delete();
        $cart1Items = [];
        foreach ($tenProductSlugs as $index => $slug) {
            $prod = $products[$slug];
            $cart1Items[] = [
                'product_id' => $prod->id,
                'product_name_snapshot' => $prod->name,
                'quantity' => 1,
                'unit_price_cents' => $prod->base_price_cents,
                'line_total_cents' => $prod->base_price_cents,
                'customization_summary' => 'Item #'.($index + 1).' · 1 kg · Standard finish',
            ];
        }
        $cart1->items()->createMany($cart1Items);
        $cart1->update([
            'total_amount_cents' => $cart1->items()->sum('line_total_cents'),
        ]);

        // Seed Customer 1 placed order with 10 products
        $order1 = Order::updateOrCreate(
            ['order_number' => 'SC-1002'],
            [
                'customer_id' => $customer1->id,
                'status' => 'completed',
                'total_amount_cents' => 0,
                'notes' => 'Placed order with 10 products for Maria Santos.',
                'scheduled_at' => now()->subDay(),
            ],
        );

        $order1->items()->delete();
        $order1Items = [];
        foreach ($tenProductSlugs as $index => $slug) {
            $prod = $products[$slug];
            $order1Items[] = [
                'product_id' => $prod->id,
                'product_name_snapshot' => $prod->name,
                'quantity' => 1,
                'unit_price_cents' => $prod->base_price_cents,
                'line_total_cents' => $prod->base_price_cents,
                'customization_summary' => 'Item #'.($index + 1).' · Bento size · Celebration finish',
            ];
        }
        $order1->items()->createMany($order1Items);
        $order1->update([
            'total_amount_cents' => $order1->items()->sum('line_total_cents'),
        ]);

        // 4. Customer 2 (Juan Dela Cruz) has 2 products
        $twoProductSlugs = [
            'classic-ube',
            'chocolate-fudge',
        ];

        // Seed Customer 2 draft cart with 2 products
        $cart2 = Order::updateOrCreate(
            ['order_number' => 'SC-2001'],
            [
                'customer_id' => $customer2->id,
                'status' => Order::CART_STATUS,
                'total_amount_cents' => 0,
                'notes' => 'Draft cart with 2 products for Juan Dela Cruz.',
                'scheduled_at' => null,
            ],
        );

        $cart2->items()->delete();
        $cart2Items = [];
        foreach ($twoProductSlugs as $index => $slug) {
            $prod = $products[$slug];
            $cart2Items[] = [
                'product_id' => $prod->id,
                'product_name_snapshot' => $prod->name,
                'quantity' => 1,
                'unit_price_cents' => $prod->base_price_cents,
                'line_total_cents' => $prod->base_price_cents,
                'customization_summary' => 'Item #'.($index + 1).' · 1 kg · Buttercream finish',
            ];
        }
        $cart2->items()->createMany($cart2Items);
        $cart2->update([
            'total_amount_cents' => $cart2->items()->sum('line_total_cents'),
        ]);

        // Seed Customer 2 placed order with 2 products
        $order2 = Order::updateOrCreate(
            ['order_number' => 'SC-2002'],
            [
                'customer_id' => $customer2->id,
                'status' => 'completed',
                'total_amount_cents' => 0,
                'notes' => 'Placed order with 2 products for Juan Dela Cruz.',
                'scheduled_at' => now()->subDays(2),
            ],
        );

        $order2->items()->delete();
        $order2Items = [];
        foreach ($twoProductSlugs as $index => $slug) {
            $prod = $products[$slug];
            $order2Items[] = [
                'product_id' => $prod->id,
                'product_name_snapshot' => $prod->name,
                'quantity' => 1,
                'unit_price_cents' => $prod->base_price_cents,
                'line_total_cents' => $prod->base_price_cents,
                'customization_summary' => 'Item #'.($index + 1).' · Bento size · Minimal finish',
            ];
        }
        $order2->items()->createMany($order2Items);
        $order2->update([
            'total_amount_cents' => $order2->items()->sum('line_total_cents'),
        ]);
    }
}

if (! class_exists('Database\Seeders\midtermlabexamseeder', false)) {
    class_alias(MidtermLabExamSeeder::class, 'Database\Seeders\midtermlabexamseeder');
}

if (! class_exists('midtermlabexamseeder', false)) {
    class_alias(MidtermLabExamSeeder::class, 'midtermlabexamseeder');
}
