<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // NOTE (change NOT in the worksheet instructions): The factory allows
        // tests to create individual cart products independently.
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name_snapshot' => fake()->words(3, true),
            'quantity' => 1,
            'unit_price_cents' => 100000,
            'line_total_cents' => 100000,
            'customization_summary' => 'Standard cake configuration',
        ];
    }
}
