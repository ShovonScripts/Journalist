<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_media_id',
        'website_url',
        'description',
    ];

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class);
    }
}
