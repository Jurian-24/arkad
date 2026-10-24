<?php

namespace App\Enums;

enum TransactionKind: string
{
    case Income = 'income';
    case Expense = 'expense';
    case InternalTransfer = 'internal_transfer';

    /**
     * Whether this kind counts towards income and expense totals.
     * An internal transfer only moves money between the user's own accounts.
     */
    public function countsInTotals(): bool
    {
        return $this !== self::InternalTransfer;
    }
}
