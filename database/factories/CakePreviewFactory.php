<?php

namespace Database\Factories;

use App\Models\CakePreview;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CakePreview>
 */
class CakePreviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // NOTE (change NOT in the worksheet instructions): The factory
        // supports isolated preview mapping tests.
        return [
            'product_id' => Product::factory(),
            'selection_key' => fake()->unique()->word(),
            'image_path' => 'images/cakes/test-preview.png',
            'alt_text' => fake()->sentence(),
        ];
    }
}
