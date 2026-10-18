<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The single source of truth for slugs (never the client). Route words are
 * reserved so a content slug can never shadow a real route, and uniqueness is
 * settled here rather than by trusting a hidden field
 * (ADMIN_PANEL.md §7, SEO.md §3).
 */
class Slug
{
    /**
     * Path segments the public site owns. A content slug may not equal one.
     *
     * @var list<string>
     */
    public const RESERVED = [
        'admin', 'api', 'about', 'archive', 'articles', 'contact', 'create',
        'edit', 'investigations', 'interviews', 'login', 'logout', 'new',
        'opinions', 'preview', 'publications', 'search', 'sections', 'topics',
        'work',
    ];

    public static function make(string $value): string
    {
        return Str::slug($value);
    }

    public static function isReserved(string $slug): bool
    {
        return in_array($slug, self::RESERVED, true);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function uniqueFor(string $modelClass, string $value, ?int $ignoreId = null): string
    {
        $model = new $modelClass;
        $table = $model->getTable();

        $base = static::make($value);

        if ($base === '') {
            $base = 'item';
        }

        if (static::isReserved($base)) {
            $base .= '-piece';
        }

        $slug = $base;
        $suffix = 1;

        while (static::exists($table, $slug, $ignoreId)) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function isAvailable(string $modelClass, string $slug, ?int $ignoreId = null): bool
    {
        if (static::isReserved($slug)) {
            return false;
        }

        $model = new $modelClass;

        return ! static::exists($model->getTable(), $slug, $ignoreId);
    }

    private static function exists(string $table, string $slug, ?int $ignoreId): bool
    {
        return DB::table($table)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
