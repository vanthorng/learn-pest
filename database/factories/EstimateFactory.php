<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estimate>
 */
class EstimateFactory extends Factory
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
            'customer_id' => Customer::factory(),
            'number' => fake()->unique()->bothify('EST-#####'),
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'discount_amount' => 0,
            'tax_rate' => 0,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
