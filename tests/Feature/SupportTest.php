<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContentItem;
use App\Support\HtmlSanitizer;
use App\Support\ReadingTime;
use App\Support\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_slugifies_and_flags_reserved_words(): void
    {
        $this->assertSame('my-great-piece', Slug::make('My Great Piece!'));
        $this->assertTrue(Slug::isReserved('admin'));
        $this->assertFalse(Slug::isReserved('my-great-piece'));
    }

    public function test_slug_unique_for_appends_a_suffix_when_taken(): void
    {
        ContentItem::factory()->create(['slug' => 'shared-title']);

        $slug = Slug::uniqueFor(ContentItem::class, 'Shared Title');

        $this->assertSame('shared-title-2', $slug);
    }

    public function test_slug_unique_for_neutralises_reserved_words(): void
    {
        $slug = Slug::uniqueFor(Category::class, 'Search');

        $this->assertSame('search-piece', $slug);
        $this->assertTrue(Slug::isAvailable(Category::class, 'search-piece'));
        $this->assertFalse(Slug::isAvailable(Category::class, 'search'));
    }

    public function test_sanitizer_strips_scripts_and_event_handlers(): void
    {
        $dirty = '<p onclick="steal()">Hello <script>alert(1)</script>'
            .'<a href="javascript:alert(1)">x</a></p>';

        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('<script', (string) $clean);
        $this->assertStringNotContainsString('onclick', (string) $clean);
        $this->assertStringNotContainsString('javascript:', (string) $clean);
        $this->assertStringContainsString('Hello', (string) $clean);
    }

    public function test_sanitizer_keeps_allowlisted_markup_and_unwraps_the_rest(): void
    {
        $clean = HtmlSanitizer::clean('<div><strong>Bold</strong> and <em>italic</em></div>');

        $this->assertStringNotContainsString('<div', (string) $clean);
        $this->assertStringContainsString('<strong>Bold</strong>', (string) $clean);
        $this->assertStringContainsString('<em>italic</em>', (string) $clean);
    }

    public function test_sanitizer_removes_frames(): void
    {
        $clean = HtmlSanitizer::clean('<p>ok</p><iframe src="https://evil.test"></iframe>');

        $this->assertStringNotContainsString('<iframe', (string) $clean);
        $this->assertStringContainsString('ok', (string) $clean);
    }

    public function test_reading_time_is_derived_from_word_count(): void
    {
        $body = '<p>'.implode(' ', array_fill(0, 400, 'word')).'</p>';

        $this->assertSame(2, ReadingTime::minutesFromHtml($body));
        $this->assertSame(1, ReadingTime::minutesFromHtml('<p>a few words only</p>'));
        $this->assertNull(ReadingTime::minutesFromHtml('<p></p>'));
        $this->assertNull(ReadingTime::minutesFromHtml(null));
    }
}
