<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * The single public-facing identity of the journalist (ADMIN_PANEL.md §12).
 *
 * This is a singleton: the public site resolves it in seven places through
 * `current()`, and `CareerHistory`/`Education`/`Award` attach to it. A second
 * row would silently split the journalist's own career across two profiles, so
 * a `creating` guard throws rather than allowing it — the model reinforces the
 * edit-only route shape below the UI.
 */
class JournalistProfile extends Model
{
    use HasFactory;

    /** The social platforms the profile form can capture. */
    public const SOCIAL_PLATFORMS = ['website', 'x', 'facebook', 'instagram', 'linkedin', 'youtube', 'email'];

    protected $fillable = [
        'user_id',
        'name',
        'title',
        'photo_media_id',
        'short_bio',
        'long_bio',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $profile): void {
            if (static::query()->exists()) {
                throw new RuntimeException('Only one journalist profile may exist.');
            }
        });
    }

    /**
     * The one profile, resolved deterministically by id.
     */
    public static function current(): ?self
    {
        return static::query()->oldest('id')->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'journalist_profile_publication');
    }

    public function careerHistory(): HasMany
    {
        return $this->hasMany(CareerHistory::class)->orderBy('sort_order')->orderBy('id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('sort_order')->orderBy('id');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    public function social(): array
    {
        $social = $this->meta['social'] ?? [];

        return array_filter(
            array_intersect_key($social, array_flip(self::SOCIAL_PLATFORMS)),
            fn ($value) => filled($value),
        );
    }

    /**
     * @return list<string>
     */
    public function beats(): array
    {
        return array_values(array_filter($this->meta['beats'] ?? []));
    }
}
