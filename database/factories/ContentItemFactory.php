<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentItem>
 */
class ContentItemFactory extends Factory
{
    protected $model = ContentItem::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'author_id' => User::factory(),
            'content_type' => ContentType::News,
            'source_type' => SourceType::Internal,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(4, true),
            'status' => ContentStatus::Draft,
            'is_featured' => false,
        ];
    }

    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => SourceType::External,
            'body' => null,
            'external_url' => fake()->url(),
            'publication_id' => PublicationFactory::new(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }
}
