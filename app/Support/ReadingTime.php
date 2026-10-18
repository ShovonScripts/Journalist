<?php

namespace App\Support;

/**
 * Derives reading time from sanitized body text (ADMIN_PANEL.md §7). Stored on
 * save so listing pages never recompute it, with the value overridable in the
 * form.
 */
class ReadingTime
{
    private const WORDS_PER_MINUTE = 200;

    public static function minutesFromHtml(?string $html): ?int
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === false || $words === []) {
            return null;
        }

        return max(1, (int) ceil(count($words) / self::WORDS_PER_MINUTE));
    }
}
