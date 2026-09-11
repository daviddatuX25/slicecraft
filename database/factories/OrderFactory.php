<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // NOTE (change NOT in the worksheet instructions): The factory
        // supports isolated order and cart-item tests.
        return [
            'order_number' => 'SC-'.fake()->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'status' => 'pending',
            'total_amount_cents' => 0,
            'notes' => null,
            'scheduled_at' => null,
        ];
    }
}
