<?php

namespace Database\Factories;

use App\Models\Education;
use App\Models\JournalistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-15 years', '-4 years');

        return [
            'journalist_profile_id' => JournalistProfile::factory(),
            'institution' => fake()->company().' University',
            'program' => fake()->randomElement(['BA', 'MA', 'MSc']).' '.fake()->word(),
            'start_date' => $start,
            'end_date' => fake()->optional()->dateTimeBetween($start, 'now'),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
