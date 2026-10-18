<?php

namespace Database\Factories;

use App\Models\CareerHistory;
use App\Models\JournalistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerHistory>
 */
class CareerHistoryFactory extends Factory
{
    protected $model = CareerHistory::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-15 years', '-2 years');

        return [
            'journalist_profile_id' => JournalistProfile::factory(),
            'role' => fake()->jobTitle(),
            'organization' => fake()->company(),
            'start_date' => $start,
            'end_date' => fake()->optional()->dateTimeBetween($start, 'now'),
            'description' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
