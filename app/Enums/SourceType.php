<?php

namespace App\Enums;

enum SourceType: string
{
    case Internal = 'internal';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'On-site',
            self::External => 'External',
        };
    }
}
