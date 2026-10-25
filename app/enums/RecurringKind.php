<?php

namespace App\Enums;

enum RecurringKind: string
{
    case Expense = 'expense';
    case Income = 'income';
    case InternalTransfer = 'internal_transfer';

    /** Only transfers need a destination account. */
    public function requiresDestinationAccount(): bool
    {
        return $this === self::InternalTransfer;
    }

    /**
     * The sign the amount should have: -1 for money out, 1 for money in,
     * 0 for a transfer (it is out on one account and in on the other).
     */
    public function expectedSign(): int
    {
        return match ($this) {
            self::Expense => -1,
            self::Income => 1,
            self::InternalTransfer => 0,
        };
    }

    /** The kind a transaction gets when it is matched to this payment. */
    public function toTransactionKind(): TransactionKind
    {
        return match ($this) {
            self::Expense => TransactionKind::Expense,
            self::Income => TransactionKind::Income,
            self::InternalTransfer => TransactionKind::InternalTransfer,
        };
    }
}
