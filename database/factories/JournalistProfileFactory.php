<?php

namespace Database\Factories;

use App\Models\JournalistProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalistProfile>
 */
class JournalistProfileFactory extends Factory
{
    protected $model = JournalistProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'title' => fake()->jobTitle(),
            'photo_media_id' => null,
            'short_bio' => fake()->sentence(),
            'long_bio' => fake()->paragraphs(2, true),
            'meta' => [
                'beats' => ['Politics', 'Human rights'],
                'social' => ['x' => 'https://x.com/example'],
            ],
        ];
    }
}
