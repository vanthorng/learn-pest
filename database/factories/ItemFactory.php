<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['product', 'service']),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'unit_price' => fake()->randomFloat(2, 1, 1_000),
            'description' => fake()->sentence(),
        ];
    }
}
