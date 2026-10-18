<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistryTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    public function test_a_category_can_be_created_with_a_derived_slug(): void
    {
        $this->actingAs($this->owner())
            ->post(route('admin.categories.store'), ['name' => 'Crime & Justice'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'crime-justice']);
    }

    public function test_a_reserved_slug_is_neutralised(): void
    {
        $this->actingAs($this->owner())
            ->post(route('admin.categories.store'), ['name' => 'Search']);

        $this->assertDatabaseHas('categories', ['slug' => 'search-piece']);
    }

    public function test_a_category_in_use_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        ContentItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->owner())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->owner())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_editing_keeps_the_current_slug_when_unchanged(): void
    {
        $category = Category::factory()->create(['name' => 'Politics', 'slug' => 'politics']);

        $this->actingAs($this->owner())
            ->put(route('admin.categories.update', $category), ['name' => 'Politics', 'slug' => 'politics'])
            ->assertRedirect();

        $this->assertSame('politics', $category->fresh()->slug);
    }
}
