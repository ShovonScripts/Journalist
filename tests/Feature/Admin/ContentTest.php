<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use App\Models\ContentItem;
use App\Models\Publication;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    private function validInternal(array $overrides = []): array
    {
        return array_merge([
            'content_type' => ContentType::News->value,
            'source_type' => SourceType::Internal->value,
            'title' => 'The Long Wait',
            'summary' => 'A summary.',
            'body' => '<p>Some real reporting body text.</p>',
            'status' => ContentStatus::Draft->value,
            'is_featured' => '0',
        ], $overrides);
    }

    public function test_internal_content_requires_a_body(): void
    {
        $this->actingAs($this->owner())
            ->post(route('admin.content.store'), $this->validInternal(['body' => '']))
            ->assertSessionHasErrors('body');
    }

    public function test_external_content_requires_a_publication_and_url_and_keeps_no_body(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post(route('admin.content.store'), $this->validInternal([
                'source_type' => SourceType::External->value,
            ]))
            ->assertSessionHasErrors(['publication_id', 'external_url']);

        $publication = Publication::factory()->create();

        $this->actingAs($owner)
            ->post(route('admin.content.store'), $this->validInternal([
                'source_type' => SourceType::External->value,
                'publication_id' => $publication->id,
                'external_url' => 'https://example.com/story',
                'body' => '<p>should be dropped</p>',
            ]))
            ->assertSessionHasErrors('body');

        $this->actingAs($owner)
            ->post(route('admin.content.store'), $this->validInternal([
                'source_type' => SourceType::External->value,
                'publication_id' => $publication->id,
                'external_url' => 'https://example.com/story',
                'body' => '',
            ]))
            ->assertRedirect();

        $item = ContentItem::query()->firstOrFail();
        $this->assertNull($item->body);
        $this->assertSame(SourceType::External, $item->source_type);
    }

    public function test_slug_is_derived_and_sanitizer_runs_on_save(): void
    {
        $this->actingAs($this->owner())
            ->post(route('admin.content.store'), $this->validInternal([
                'title' => 'My Great Story',
                'slug' => '',
                'body' => '<p>Keep</p><script>alert(1)</script><a href="javascript:x()">x</a>',
            ]))
            ->assertRedirect();

        $item = ContentItem::query()->firstOrFail();
        $this->assertSame('my-great-story', $item->slug);
        $this->assertStringNotContainsString('<script', $item->body);
        $this->assertStringNotContainsString('javascript:', $item->body);
        $this->assertStringContainsString('Keep', $item->body);
    }

    public function test_publishing_in_the_future_becomes_scheduled(): void
    {
        $this->actingAs($this->owner())
            ->post(route('admin.content.store'), $this->validInternal([
                'status' => ContentStatus::Published->value,
                'published_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            ]))
            ->assertRedirect();

        $this->assertSame(ContentStatus::Scheduled, ContentItem::query()->firstOrFail()->status);
    }

    public function test_content_can_attach_tags_topics_and_related(): void
    {
        $owner = $this->owner();
        $tags = Tag::factory()->count(2)->create();
        $topic = Topic::factory()->create();
        $other = ContentItem::factory()->create();

        $this->actingAs($owner)
            ->post(route('admin.content.store'), $this->validInternal([
                'tags' => $tags->pluck('id')->all(),
                'topics' => [$topic->id],
                'related' => [$other->id],
            ]))
            ->assertRedirect();

        $item = ContentItem::query()->where('title', 'The Long Wait')->firstOrFail();
        $this->assertCount(2, $item->tags);
        $this->assertCount(1, $item->topics);
        $this->assertCount(1, $item->related);
    }

    public function test_bulk_publish_updates_selected_items(): void
    {
        $owner = $this->owner();
        $items = ContentItem::factory()->count(2)->create(['status' => ContentStatus::Draft]);

        $this->actingAs($owner)
            ->post(route('admin.content.bulk'), [
                'action' => 'publish',
                'ids' => $items->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertSame(2, ContentItem::query()->where('status', ContentStatus::Published)->count());
    }

    public function test_slug_check_reports_availability(): void
    {
        ContentItem::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->owner())
            ->getJson(route('admin.content.slug-check', ['slug' => 'taken']))
            ->assertOk()
            ->assertJson(['available' => false]);

        $this->actingAs($this->owner())
            ->getJson(route('admin.content.slug-check', ['slug' => 'free']))
            ->assertOk()
            ->assertJson(['available' => true]);
    }

    public function test_out_of_range_page_returns_404(): void
    {
        ContentItem::factory()->create();

        $this->actingAs($this->owner())
            ->get(route('admin.content.index', ['page' => 2]))
            ->assertNotFound();
    }

    public function test_sort_injection_falls_back_to_the_default_column(): void
    {
        ContentItem::factory()->count(2)->create();

        $this->actingAs($this->owner())
            ->get(route('admin.content.index', ['sort' => 'password']))
            ->assertOk();
    }
}
