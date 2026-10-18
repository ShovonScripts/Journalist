<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'journalist_profile_id',
        'title',
        'awarding_body',
        'year',
        'description',
        'url',
        'media_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(JournalistProfile::class, 'journalist_profile_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
