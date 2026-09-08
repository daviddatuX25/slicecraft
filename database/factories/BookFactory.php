<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        // Pick a random cover from the 20 downloaded book images
        $covers = [
            'book_1.jpg', 'book_2.jpg', 'book_3.jpg', 'book_4.jpg', 'book_5.jpg',
            'book_6.jpg', 'book_7.jpg', 'book_8.jpg', 'book_9.jpg', 'book_10.jpg',
            'book_11.jpg', 'book_12.jpg', 'book_13.jpg', 'book_14.jpg', 'book_15.jpg',
            'book_16.jpg', 'book_17.jpg', 'book_18.jpg', 'book_19.jpg', 'book_20.jpg',
        ];

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'country_id' => $this->faker->numberBetween(1, 100),
            'stock' => $this->faker->numberBetween(1, 1000),             'amount' =>$this->faker->randomFloat(2, 10, 9999),
            'photo' => $this->faker->randomElement($covers),
        ];
    }
}
