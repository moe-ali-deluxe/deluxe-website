<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'image' => 'default.jpg',
            'is_active' => true,
            'category' => $this->faker->randomElement(['Dental', 'Medical', 'Equipment']),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
        ];
    }
}
