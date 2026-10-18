<?php

namespace Tests\Feature\Admin;

use App\Models\Award;
use App\Models\CareerHistory;
use App\Models\Category;
use App\Models\ContentItem;
use App\Models\Education;
use App\Models\JournalistProfile;
use App\Models\Media;
use App\Models\Publication;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every admin screen an owner can reach, so a Blade regression fails a
 * test rather than a page (ADMIN_PANEL.md §17).
 */
class ScreenRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_screen_renders(): void
    {
        $owner = User::factory()->owner()->create();

        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $topic = Topic::factory()->create();
        $publication = Publication::factory()->create();
        $media = Media::factory()->create();
        $item = ContentItem::factory()->create([
            'category_id' => $category->id,
            'author_id' => $owner->id,
        ]);

        $profile = JournalistProfile::factory()->create(['user_id' => $owner->id]);
        $career = CareerHistory::factory()->create(['journalist_profile_id' => $profile->id]);
        $education = Education::factory()->create(['journalist_profile_id' => $profile->id]);
        $award = Award::factory()->create(['journalist_profile_id' => $profile->id]);

        $urls = [
            route('admin.dashboard'),
            route('admin.content.index'),
            route('admin.content.create'),
            route('admin.content.edit', $item),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
            route('admin.tags.index'),
            route('admin.tags.create'),
            route('admin.tags.edit', $tag),
            route('admin.topics.index'),
            route('admin.topics.create'),
            route('admin.topics.edit', $topic),
            route('admin.publications.index'),
            route('admin.publications.create'),
            route('admin.publications.edit', $publication),
            route('admin.media.index'),
            route('admin.media.edit', $media),
            route('admin.journalist-profile.edit', $profile),
            route('admin.career-history.index'),
            route('admin.career-history.create'),
            route('admin.career-history.edit', $career),
            route('admin.education.index'),
            route('admin.education.create'),
            route('admin.education.edit', $education),
            route('admin.awards.index'),
            route('admin.awards.create'),
            route('admin.awards.edit', $award),
            route('admin.profile.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }
}
