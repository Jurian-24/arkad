<?php

namespace App\Enums;

enum RecurringStatus: string
{
    // Found automatically in the imported transactions, waiting for the user to confirm.
    case Suggested = 'suggested';
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';

    /** Whether this payment counts towards the expected monthly costs. */
    public function countsInBudget(): bool
    {
        return $this === self::Active;
    }

    /** Whether the payment is finished and should not change anymore. */
    public function isFinal(): bool
    {
        return $this === self::Ended;
    }

    /**
     * The statuses a payment may move to from this one. Use it to reject
     * impossible changes, such as reviving an ended payment or activating
     * a suggestion that the user has not looked at.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Suggested => [self::Active, self::Ended],
            self::Active => [self::Paused, self::Ended],
            self::Paused => [self::Active, self::Ended],
            self::Ended => [],
        };
    }

    public function canBecome(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
