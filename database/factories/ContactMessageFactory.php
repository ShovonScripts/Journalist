<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->optional()->sentence(4),
            'message' => fake()->paragraph(),
            'is_read' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['is_read' => true]);
    }
}
