<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'content_type',
        'source_type',
        'title',
        'slug',
        'summary',
        'body',
        'featured_image_media_id',
        'category_id',
        'publication_id',
        'external_url',
        'interviewee_name',
        'interviewee_title',
        'video_url',
        'meta',
        'is_featured',
        'status',
        'published_at',
        'reading_time_minutes',
        'seo_title',
        'seo_description',
        'seo_og_image_media_id',
        'canonical_url_override',
    ];

    protected function casts(): array
    {
        return [
            'content_type' => ContentType::class,
            'source_type' => SourceType::class,
            'status' => ContentStatus::class,
            'meta' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_media_id');
    }

    public function seoOgImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_og_image_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_item_tag');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'content_item_topic');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            ContentItem::class,
            'related_content',
            'content_item_id',
            'related_content_item_id',
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published->value)
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Draft->value);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Scheduled->value);
    }

    public function isExternal(): bool
    {
        return $this->source_type === SourceType::External;
    }

    public function isInternal(): bool
    {
        return $this->source_type === SourceType::Internal;
    }
}
