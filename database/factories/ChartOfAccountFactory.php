<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
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
            'code' => (string) fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
        ];
    }
}
