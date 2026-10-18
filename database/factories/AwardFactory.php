<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\JournalistProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Award>
 */
class AwardFactory extends Factory
{
    protected $model = Award::class;

    public function definition(): array
    {
        return [
            'journalist_profile_id' => JournalistProfile::factory(),
            'title' => fake()->words(3, true),
            'awarding_body' => fake()->company(),
            'year' => fake()->numberBetween(2005, 2026),
            'description' => fake()->optional()->sentence(),
            'url' => fake()->optional()->url(),
            'media_id' => null,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
