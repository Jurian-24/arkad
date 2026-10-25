<?php

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Deposit = 'deposit';
    case Investment = 'investment';
    case Loan = 'loan';

    /** Whether this account type has contract terms (rate, maturity). */
    public function hasTerms(): bool
    {
        return $this === self::Deposit;
    }

    /** Whether the balance is money the user owns, as opposed to debt. */
    public function isAsset(): bool
    {
        return $this !== self::Loan;
    }
}
