<?php

namespace Tests\Feature;

use App\Enums\TransactionKind;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Database\Factories\AccountFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TransactionScopesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = AccountFactory::new()->create(['user_id' => $this->user->id]);
    }

    private function tx(float $amount, TransactionKind $kind, string $date = '2026-10-10'): Transaction
    {
        return TransactionFactory::new()->create([
            'account_id' => $this->account->id,
            'user_id' => $this->user->id,
            'amount' => $amount,
            'kind' => $kind,
            'booked_at' => $date,
        ]);
    }

    private function mine()
    {
        return Transaction::where('user_id', $this->user->id);
    }

    public function test_internal_transfers_do_not_count_in_totals(): void
    {
        $this->tx(1000, TransactionKind::Income);
        $this->tx(-200, TransactionKind::Expense);
        $this->tx(-500, TransactionKind::InternalTransfer);

        $this->assertEquals(800, (float) $this->mine()->countingInTotals()->sum('amount'));
    }

    public function test_income_and_expense_scopes(): void
    {
        $this->tx(1000, TransactionKind::Income);
        $this->tx(-200, TransactionKind::Expense);
        $this->tx(-300, TransactionKind::Expense);
        $this->tx(-500, TransactionKind::InternalTransfer);

        $this->assertEquals(1000, (float) $this->mine()->income()->sum('amount'));
        $this->assertEquals(-500, (float) $this->mine()->expenses()->sum('amount'));
        $this->assertSame(1, $this->mine()->internalTransfers()->count());
    }

    public function test_a_refund_reduces_the_total_spent(): void
    {
        $this->tx(-50, TransactionKind::Expense);
        $this->tx(20, TransactionKind::Expense); // refund: positive amount, still an expense row

        $this->assertEquals(-30, (float) $this->mine()->expenses()->sum('amount'));
    }

    public function test_in_month_includes_the_first_and_last_day_but_nothing_outside(): void
    {
        $this->tx(-1, TransactionKind::Expense, '2026-09-30');
        $this->tx(-1, TransactionKind::Expense, '2026-10-01');
        $this->tx(-1, TransactionKind::Expense, '2026-10-31');
        $this->tx(-1, TransactionKind::Expense, '2026-11-01');

        $this->assertSame(2, $this->mine()->inMonth(Carbon::parse('2026-10-15'))->count());
    }

    public function test_in_month_does_not_change_the_date_that_is_passed_in(): void
    {
        $date = Carbon::parse('2026-10-15');

        $this->mine()->inMonth($date)->count();

        $this->assertSame('2026-10-15', $date->toDateString());
    }

    public function test_incoming_and_outgoing_follow_the_sign_of_the_amount(): void
    {
        $in = $this->tx(10, TransactionKind::Income);
        $out = $this->tx(-10, TransactionKind::Expense);

        $this->assertTrue($in->isIncoming());
        $this->assertFalse($in->isOutgoing());
        $this->assertTrue($out->isOutgoing());
        $this->assertFalse($out->isIncoming());
    }
}
