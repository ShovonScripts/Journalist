<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'type',
        'file_path',
        'disk',
        'original_filename',
        'alt_text',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('type', MediaType::Image->value);
    }

    public function scopeMissingAlt(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('alt_text')->orWhere('alt_text', '');
        });
    }
}
