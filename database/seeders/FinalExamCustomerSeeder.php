<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinalExamCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds for the Final Exam customer experience.
     */
    public function run(): void
    {
        // 1. Primary demo customer
        $customer = Customer::firstOrCreate(
            ['email' => 'customer@slicecraft.test'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password123'),
                'phone' => '0917-888-9999',
                'address' => '123 Heritage Village, Vigan City, Ilocos Sur',
                'status' => 'active',
            ]
        );

        // 2. Secondary customer (for demonstrating 403 privacy defense)
        $secondary = Customer::firstOrCreate(
            ['email' => 'juan@slicecraft.test'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password123'),
                'phone' => '0918-777-6666',
                'address' => 'San Fernando, La Union',
                'status' => 'active',
            ]
        );

        // 3. Ensure sample products exist
        $ube = Product::firstOrCreate(
            ['slug' => 'classic-ube'],
            [
                'name' => 'Classic Ube',
                'description' => 'Traditional Filipino purple yam sponge cake with signature ube halaya frosting.',
                'category' => 'Cakes',
                'base_price_cents' => 75000,
                'image_path' => 'img/cake_ube.png',
                'is_active' => true,
            ]
        );

        $choco = Product::firstOrCreate(
            ['slug' => 'chocolate-fudge'],
            [
                'name' => 'Chocolate Fudge',
                'description' => 'Decadent multi-layered Belgian dark chocolate sponge with rich fudge ganache.',
                'category' => 'Cakes',
                'base_price_cents' => 85000,
                'image_path' => 'img/cake_chocolate.png',
                'is_active' => true,
            ]
        );

        // 4. Sample placed order for demo customer
        $order = Order::firstOrCreate(
            ['order_number' => 'ORD-DEMO-2026'],
            [
                'customer_id' => $customer->id,
                'status' => 'processing',
                'total_amount_cents' => 160000,
                'notes' => 'Happy Graduation Celebration! Please include candles.',
                'scheduled_at' => now()->addDays(2),
            ]
        );

        if ($order->items()->count() === 0) {
            $order->items()->create([
                'product_id' => $ube->id,
                'product_name_snapshot' => $ube->name,
                'quantity' => 1,
                'unit_price_cents' => 75000,
                'line_total_cents' => 75000,
                'customization_summary' => 'Medium (8 inch) • Ube Halaya Filling',
            ]);

            $order->items()->create([
                'product_id' => $choco->id,
                'product_name_snapshot' => $choco->name,
                'quantity' => 1,
                'unit_price_cents' => 85000,
                'line_total_cents' => 85000,
                'customization_summary' => 'Medium (8 inch) • Dark Ganache',
            ]);
        }
    }
}
