<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $filename = fake()->uuid().'.jpg';

        return [
            'type' => MediaType::Image,
            'file_path' => 'media/2026/10/'.$filename,
            'disk' => 'public',
            'original_filename' => $filename,
            'alt_text' => fake()->sentence(),
            'caption' => null,
            'uploaded_by' => User::factory(),
        ];
    }

    public function missingAlt(): static
    {
        return $this->state(fn (array $attributes) => ['alt_text' => null]);
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::Document,
            'file_path' => 'media/2026/10/'.fake()->uuid().'.pdf',
        ]);
    }
}
