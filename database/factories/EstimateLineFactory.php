<?php

namespace Database\Factories;

use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstimateLine>
 */
class EstimateLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estimate_id' => Estimate::factory(),
            'item_id' => Item::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ];
    }
}
