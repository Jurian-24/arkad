<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum Frequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    /** How many times per year a payment with this frequency happens (interval of 1). */
    public function periodsPerYear(): float
    {
        return match ($this) {
            self::Weekly => 52,
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Yearly => 1,
        };
    }

    /**
     * Move a date forward by $count periods of this frequency.
     *
     * The "NoOverflow" variants keep a payment on the 31st inside short
     * months (Jan 31 + 1 month = Feb 28, not Mar 3). Call it with a fresh
     * copy of the anchor date and a growing $count, not repeatedly on the
     * previous result, so the day does not drift.
     */
    public function advance(Carbon $date, int $count = 1): Carbon
    {
        return match ($this) {
            self::Weekly => $date->addWeeks($count),
            self::Monthly => $date->addMonthsNoOverflow($count),
            self::Quarterly => $date->addMonthsNoOverflow(3 * $count),
            self::Yearly => $date->addYearsNoOverflow($count),
        };
    }
}
