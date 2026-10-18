<?php

namespace App\Support;

use App\Models\ContentItem;
use App\Models\Media;
use App\Models\Publication;
use App\Models\Topic;

/**
 * Answers "is this media row referenced anywhere?" so deletion can be blocked
 * while an image is in use (ADMIN_PANEL.md §9) — a broken featured image on a
 * published investigation is worse than the inconvenience of unsetting it
 * first.
 */
class MediaUsage
{
    /**
     * @return list<string> human-readable references
     */
    public static function references(Media $media): array
    {
        $id = $media->id;
        $references = [];

        $contentCount = ContentItem::query()
            ->where('featured_image_media_id', $id)
            ->orWhere('seo_og_image_media_id', $id)
            ->count();

        if ($contentCount > 0) {
            $references[] = $contentCount.' content item'.($contentCount === 1 ? '' : 's');
        }

        $topicCount = Topic::query()->where('featured_image_media_id', $id)->count();

        if ($topicCount > 0) {
            $references[] = $topicCount.' topic'.($topicCount === 1 ? '' : 's');
        }

        $publicationCount = Publication::query()->where('logo_media_id', $id)->count();

        if ($publicationCount > 0) {
            $references[] = $publicationCount.' publication'.($publicationCount === 1 ? '' : 's');
        }

        return $references;
    }

    public static function isReferenced(Media $media): bool
    {
        return self::references($media) !== [];
    }
}
