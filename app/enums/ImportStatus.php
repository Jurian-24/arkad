<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';

    /** Whether the batch is done, successfully or not. */
    public function isFinished(): bool
    {
        return $this !== self::Pending;
    }
}
