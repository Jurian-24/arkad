<?php

namespace App\Enums;

enum MatchType: string
{
    case Contains = 'contains';
    case Equals = 'equals';
    case Regex = 'regex';

    /**
     * Whether $value matches $pattern with this match type.
     * Comparisons are case-insensitive, since bank descriptions vary in case.
     */
    public function matches(string $value, string $pattern): bool
    {
        return match ($this) {
            self::Contains => str_contains(mb_strtolower($value), mb_strtolower($pattern)),
            self::Equals => mb_strtolower($value) === mb_strtolower($pattern),
            // The @ hides the warning for an invalid pattern, which then counts as no match.
            self::Regex => @preg_match('/' . str_replace('/', '\/', $pattern) . '/iu', $value) === 1,
        };
    }
}
