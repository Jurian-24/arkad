<?php

namespace App\Enums;

use App\Models\AccountBalanceSnapshot;
use App\Models\User;

enum TabletUnlockCondition: string
{
    case FirstImport = 'first_import';               // a CSV has been imported
    case AccountsAdded = 'accounts_added';           // threshold = number of accounts
    case MonthsOfData = 'months_of_data';            // threshold = number of months with transactions
    case RecurringConfirmed = 'recurring_confirmed'; // an active recurring payment exists
    case FirstGoal = 'first_goal';                   // a goal has been created
    case PaidYourselfFirst = 'paid_yourself_first';  // threshold = percentage of income saved

    /** Whether the user meets this condition. */
    public function isMetBy(User $user, ?int $threshold = null): bool
    {
        return match ($this) {
            self::FirstImport => $user->importBatches()
                ->where('status', ImportStatus::Processed)
                ->exists(),

            self::AccountsAdded => $user->accounts()->count() >= ($threshold ?? 1),

            self::MonthsOfData => (int) $user->transactions()
                ->selectRaw("COUNT(DISTINCT DATE_FORMAT(booked_at, '%Y-%m')) as months")
                ->value('months') >= ($threshold ?? 1),

            self::RecurringConfirmed => $user->recurringPayments()->active()->exists(),

            self::FirstGoal => $user->goals()->exists(),

            self::PaidYourselfFirst => $this->savedAtLeast($user, $threshold ?? 10),
        };
    }

    /**
     * Whether the user put at least $pct percent of last month's income
     * into savings. The money put in is read from the balance snapshots
     * (their "contributions"), since savings and investment accounts often
     * have no imported transactions.
     */
    private function savedAtLeast(User $user, int $pct): bool
    {
        $month = now()->subMonth();

        $income = (float) $user->transactions()->income()->inMonth($month)->sum('amount');

        if ($income <= 0) {
            return false;
        }

        $saved = (float) AccountBalanceSnapshot::query()
            ->whereHas('account', fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('as_of_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('contributions');

        return $saved / $income * 100 >= $pct;
    }
}
