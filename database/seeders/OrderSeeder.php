<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE (change NOT in the worksheet instructions): Separate draft-cart
        // and placed-order records make the two shop concepts visible without
        // adding another table to the exam-sized application.
        $customer = Customer::where('email', 'maria@slicecraft.test')->firstOrFail();
        $products = Product::whereIn('slug', ['classic-ube', 'chocolate-fudge', 'red-velvet'])->get()->keyBy('slug');

        $cart = Order::updateOrCreate(
            ['order_number' => 'SC-1001'],
            [
                'customer_id' => $customer->id,
                'status' => Order::CART_STATUS,
                'total_amount_cents' => 0,
                'notes' => 'Demo draft cart for the Page 3 cart rubric.',
                'scheduled_at' => null,
            ],
        );

        $cart->items()->delete();

        $cart->items()->createMany([
            [
                'product_id' => $products['classic-ube']->id,
                'product_name_snapshot' => $products['classic-ube']->name,
                'quantity' => 1,
                'unit_price_cents' => 119000,
                'line_total_cents' => 119000,
                'customization_summary' => 'Bento size • Fresh fruit • Minimal celebration finish',
            ],
            [
                'product_id' => $products['chocolate-fudge']->id,
                'product_name_snapshot' => $products['chocolate-fudge']->name,
                'quantity' => 1,
                'unit_price_cents' => 110000,
                'line_total_cents' => 110000,
                'customization_summary' => '1 kg • Chocolate drip • Celebration trim',
            ],
        ]);

        $cart->update([
            'total_amount_cents' => $cart->items()->sum('line_total_cents'),
        ]);

        $order = Order::updateOrCreate(
            ['order_number' => 'SC-1002'],
            [
                'customer_id' => $customer->id,
                'status' => 'pending',
                'total_amount_cents' => 0,
                'notes' => 'Demo placed order for the order-history view.',
                'scheduled_at' => null,
            ],
        );

        $order->items()->delete();
        $order->items()->create([
            'product_id' => $products['red-velvet']->id,
            'product_name_snapshot' => $products['red-velvet']->name,
            'quantity' => 1,
            'unit_price_cents' => 125000,
            'line_total_cents' => 125000,
            'customization_summary' => '1 kg • Cream cheese finish • Celebration trim',
        ]);

        $order->update([
            'total_amount_cents' => $order->items()->sum('line_total_cents'),
        ]);
    }
}
