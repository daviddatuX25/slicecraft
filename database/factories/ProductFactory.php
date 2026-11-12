<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // NOTE (change NOT in the worksheet instructions): The factory keeps
        // Product feature tests easy to create without hand-built records.
        return [
            'name' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'category' => 'Classic Cakes',
            'base_price_cents' => fake()->numberBetween(50000, 150000),
            'image_path' => config('cake.unavailable_image'),
            'is_active' => true,
        ];
    }
}
