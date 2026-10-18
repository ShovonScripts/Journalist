<?php

namespace Tests\Feature\Admin;

use App\Models\ContentItem;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    public function test_a_real_image_is_accepted_and_stored(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner())
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('portrait.jpg', 400, 300),
                'alt_text' => 'A portrait',
            ])
            ->assertRedirect();

        $media = Media::query()->firstOrFail();

        $this->assertSame('portrait.jpg', $media->original_filename);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_a_spoofed_extension_is_rejected_by_byte_sniffing(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner())
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->createWithContent('not-really.jpg', 'just text'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_media_in_use_cannot_be_deleted(): void
    {
        Storage::fake('public');

        $media = Media::factory()->create(['file_path' => 'media/2026/10/in-use.webp']);
        ContentItem::factory()->create(['featured_image_media_id' => $media->id]);

        $this->actingAs($this->owner())
            ->delete(route('admin.media.destroy', $media))
            ->assertSessionHasErrors('media');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_unused_media_can_be_deleted(): void
    {
        Storage::fake('public');

        $media = Media::factory()->create(['file_path' => 'media/2026/10/free.webp']);
        Storage::disk('public')->put($media->file_path, 'bytes');

        $this->actingAs($this->owner())
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->file_path);
    }

    public function test_the_picker_endpoint_searches_images(): void
    {
        Media::factory()->create(['original_filename' => 'sunset.jpg', 'alt_text' => 'A sunset']);

        $this->actingAs($this->owner())
            ->getJson(route('admin.media.pick', ['q' => 'sunset']))
            ->assertOk()
            ->assertJsonCount(1);
    }
}
