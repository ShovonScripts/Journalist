<?php

namespace App\Enums;

enum ContentType: string
{
    case News = 'news';
    case Investigation = 'investigation';
    case Interview = 'interview';
    case Opinion = 'opinion';
    case Video = 'video';
    case PhotoStory = 'photo_story';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::News => 'News Article',
            self::Investigation => 'Investigation',
            self::Interview => 'Interview',
            self::Opinion => 'Opinion / Analysis',
            self::Video => 'Video',
            self::PhotoStory => 'Photo Story',
            self::Other => 'Other Work',
        };
    }
}
