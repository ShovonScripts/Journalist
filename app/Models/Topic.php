<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'featured_image_media_id',
        'sort_order',
    ];

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_media_id');
    }

    public function contentItems(): BelongsToMany
    {
        return $this->belongsToMany(ContentItem::class, 'content_item_topic');
    }
}
