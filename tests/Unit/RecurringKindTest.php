<?php

namespace Tests\Unit;

use App\Enums\RecurringKind;
use App\Enums\TransactionKind;
use PHPUnit\Framework\TestCase;

class RecurringKindTest extends TestCase
{
    public function test_only_transfers_need_a_destination_account(): void
    {
        $this->assertTrue(RecurringKind::InternalTransfer->requiresDestinationAccount());
        $this->assertFalse(RecurringKind::Expense->requiresDestinationAccount());
        $this->assertFalse(RecurringKind::Income->requiresDestinationAccount());
    }

    public function test_expected_sign(): void
    {
        $this->assertSame(-1, RecurringKind::Expense->expectedSign());
        $this->assertSame(1, RecurringKind::Income->expectedSign());
        $this->assertSame(0, RecurringKind::InternalTransfer->expectedSign());
    }

    public function test_every_recurring_kind_maps_to_a_transaction_kind(): void
    {
        $this->assertSame(TransactionKind::Expense, RecurringKind::Expense->toTransactionKind());
        $this->assertSame(TransactionKind::Income, RecurringKind::Income->toTransactionKind());
        $this->assertSame(TransactionKind::InternalTransfer, RecurringKind::InternalTransfer->toTransactionKind());
    }

    public function test_transfers_do_not_count_in_income_and_expense_totals(): void
    {
        $this->assertFalse(TransactionKind::InternalTransfer->countsInTotals());
        $this->assertTrue(TransactionKind::Income->countsInTotals());
        $this->assertTrue(TransactionKind::Expense->countsInTotals());
    }
}
