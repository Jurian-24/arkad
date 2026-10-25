<?php

namespace App\Enums;

enum RecurringSource: string
{
    // Entered by the user.
    case Manual = 'manual';
    // Found automatically in the imported transactions.
    case Detected = 'detected';

    /** Detected payments need the user's confirmation before they count. */
    public function needsConfirmation(): bool
    {
        return $this === self::Detected;
    }
}
