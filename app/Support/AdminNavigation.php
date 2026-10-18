<?php

namespace App\Support;

use App\Models\JournalistProfile;
use Illuminate\Support\Facades\Route;

/**
 * The single source of truth for the admin sidebar (ADMIN_PANEL.md §4, §5).
 * The desktop sidebar and the mobile drawer both render from this array so
 * they cannot drift.
 *
 * Items whose route does not exist yet (screens still to be built per the
 * build order in ADMIN_PANEL.md §16) render as disabled rather than as a
 * broken link — the panel stays honest about what is wired up.
 */
class AdminNavigation
{
    /**
     * @return list<array{label?: string, items: list<array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            [
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                ],
            ],
            [
                'label' => 'Content',
                'items' => [
                    ['label' => 'All Content', 'route' => 'admin.content.index'],
                    ['label' => 'News Articles', 'route' => 'admin.content.index', 'params' => ['type' => 'news']],
                    ['label' => 'Investigations', 'route' => 'admin.content.index', 'params' => ['type' => 'investigation']],
                    ['label' => 'Interviews', 'route' => 'admin.content.index', 'params' => ['type' => 'interview']],
                    ['label' => 'Opinions / Analysis', 'route' => 'admin.content.index', 'params' => ['type' => 'opinion']],
                    ['label' => 'Multimedia', 'route' => 'admin.content.index', 'params' => ['type' => 'video']],
                    ['label' => 'Categories', 'route' => 'admin.categories.index'],
                    ['label' => 'Tags', 'route' => 'admin.tags.index'],
                    ['label' => 'Topics', 'route' => 'admin.topics.index'],
                    ['label' => 'Publications', 'route' => 'admin.publications.index'],
                ],
            ],
            [
                'label' => 'Journalist',
                'items' => [
                    ['label' => 'Profile', 'route' => 'admin.journalist-profile.edit'],
                    ['label' => 'Career History', 'route' => 'admin.career-history.index'],
                    ['label' => 'Education', 'route' => 'admin.education.index'],
                    ['label' => 'Awards', 'route' => 'admin.awards.index'],
                ],
            ],
            [
                'label' => 'Media',
                'items' => [
                    ['label' => 'Media Library', 'route' => 'admin.media.index'],
                ],
            ],
            [
                'label' => 'Outreach',
                'items' => [
                    ['label' => 'Messages', 'route' => 'admin.contacts.index'],
                ],
            ],
            [
                'label' => 'SEO',
                'items' => [
                    ['label' => 'Redirects', 'route' => 'admin.redirects.index'],
                    ['label' => 'Global SEO Defaults', 'route' => 'admin.settings.edit'],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    ['label' => 'Settings', 'route' => 'admin.settings.edit'],
                    ['label' => 'Activity', 'route' => 'admin.activity.index'],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function isAvailable(array $item): bool
    {
        if (! isset($item['route']) || ! Route::has($item['route'])) {
            return false;
        }

        // The singleton profile screen has no URL until the profile exists.
        if ($item['route'] === 'admin.journalist-profile.edit') {
            return JournalistProfile::current() !== null;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function isActive(array $item): bool
    {
        if (! self::isAvailable($item)) {
            return false;
        }

        return request()->routeIs($item['route']);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function url(array $item): string
    {
        if (! self::isAvailable($item)) {
            return '#';
        }

        return route($item['route'], $item['params'] ?? self::singletonParams($item['route']));
    }

    /**
     * @return array<string, int>
     */
    private static function singletonParams(string $route): array
    {
        return match ($route) {
            'admin.journalist-profile.edit' => ['journalistProfile' => (int) JournalistProfile::current()?->id],
            default => [],
        };
    }
}
