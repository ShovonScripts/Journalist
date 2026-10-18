<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\ContentItem;
use App\Models\Media;
use App\Models\Publication;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_item_casts_its_enums_and_flags(): void
    {
        $item = ContentItem::factory()->create();

        $this->assertInstanceOf(ContentType::class, $item->content_type);
        $this->assertInstanceOf(SourceType::class, $item->source_type);
        $this->assertInstanceOf(ContentStatus::class, $item->status);
        $this->assertTrue($item->isInternal());
        $this->assertFalse($item->isExternal());
    }

    public function test_content_item_attaches_category_tags_topics_and_related(): void
    {
        $item = ContentItem::factory()->create();
        $other = ContentItem::factory()->create();

        $item->category()->associate(Category::factory()->create());
        $item->tags()->attach(Tag::factory()->count(2)->create());
        $item->topics()->attach(Topic::factory()->create());
        $item->related()->attach($other);
        $item->save();

        $this->assertInstanceOf(Category::class, $item->fresh()->category);
        $this->assertCount(2, $item->fresh()->tags);
        $this->assertCount(1, $item->fresh()->topics);
        $this->assertCount(1, $item->fresh()->related);
        $this->assertSame($other->id, $item->fresh()->related->first()->id);
    }

    public function test_published_scope_excludes_drafts_and_future_items(): void
    {
        $published = ContentItem::factory()->published()->create();
        ContentItem::factory()->create();
        ContentItem::factory()->scheduled()->create();

        $results = ContentItem::query()->published()->pluck('id');

        $this->assertEquals([$published->id], $results->all());
    }

    public function test_external_item_carries_a_publication_and_no_body(): void
    {
        $item = ContentItem::factory()->external()->create();

        $this->assertTrue($item->isExternal());
        $this->assertNull($item->body);
        $this->assertInstanceOf(Publication::class, $item->publication);
    }

    public function test_media_scopes_for_images_and_missing_alt(): void
    {
        Media::factory()->create(['alt_text' => 'Described']);
        Media::factory()->missingAlt()->create();
        Media::factory()->document()->create();

        $this->assertCount(2, Media::query()->images()->get());
        $this->assertCount(1, Media::query()->missingAlt()->get());
    }

    public function test_user_owns_content_items(): void
    {
        $user = User::factory()->owner()->create();
        ContentItem::factory()->count(3)->create(['author_id' => $user->id]);

        $this->assertCount(3, $user->contentItems);
    }

    public function test_contact_messages_can_be_filtered_to_unread(): void
    {
        ContactMessage::factory()->create();
        ContactMessage::factory()->read()->create();

        $this->assertCount(1, ContactMessage::query()->unread()->get());
    }

    public function test_settings_round_trip_typed_values(): void
    {
        Setting::put('site.name', 'Emrul Hasan Bappi');
        Setting::put('socials', ['x' => '@ehb', 'linkedin' => 'ehb']);

        $this->assertSame('Emrul Hasan Bappi', Setting::get('site.name'));
        $this->assertSame(['x' => '@ehb', 'linkedin' => 'ehb'], Setting::get('socials'));
        $this->assertSame('fallback', Setting::get('missing', 'fallback'));
    }
}
