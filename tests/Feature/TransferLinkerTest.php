<?php

namespace Tests\Feature;

use App\Enums\TransactionKind;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransferLinker;
use Database\Factories\AccountFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransferLinkerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $checking;
    private Account $deposit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->checking = AccountFactory::new()->create([
            'user_id' => $this->user->id,
            'iban' => 'NL91ABNA0417164300',
        ]);

        $this->deposit = AccountFactory::new()->deposit()->create([
            'user_id' => $this->user->id,
            'iban' => 'NL02ABNA0123456789',
        ]);
    }

    private function tx(Account $account, float $amount, string $date, ?string $counterpartyIban = null, array $extra = []): Transaction
    {
        return TransactionFactory::new()->create($extra + [
            'account_id' => $account->id,
            'user_id' => $this->user->id,
            'amount' => $amount,
            'booked_at' => $date,
            'kind' => $amount < 0 ? TransactionKind::Expense : TransactionKind::Income,
            'counterparty_iban' => $counterpartyIban,
        ]);
    }

    private function link(Transaction ...$transactions): void
    {
        (new TransferLinker())->link($this->user, collect($transactions));
    }

    public function test_a_payment_to_one_of_your_own_accounts_becomes_an_internal_transfer(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);

        $this->link($out);

        $this->assertSame(TransactionKind::InternalTransfer, $out->fresh()->kind);
    }

    public function test_without_a_counterpart_the_transfer_has_no_group_yet(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);

        $this->link($out);

        $this->assertNull($out->fresh()->transfer_group_id);
    }

    public function test_both_sides_get_the_same_group_and_kind(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);
        $in = $this->tx($this->deposit, 500, '2026-10-02', $this->checking->iban);

        $this->link($out);

        $out->refresh();
        $in->refresh();

        $this->assertNotNull($out->transfer_group_id);
        $this->assertSame($out->transfer_group_id, $in->transfer_group_id);
        $this->assertSame(TransactionKind::InternalTransfer, $in->kind);
        $this->assertSame($in->id, $out->transferPartner()->id);
    }

    public function test_a_counterpart_imported_later_is_linked_on_the_next_run(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);
        $this->link($out);

        $in = $this->tx($this->deposit, 500, '2026-10-01', $this->checking->iban);
        $this->link($in);

        $this->assertNotNull($in->fresh()->transfer_group_id);
        $this->assertSame($out->fresh()->transfer_group_id, $in->fresh()->transfer_group_id);
    }

    public function test_a_counterpart_more_than_three_days_away_is_not_linked(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);
        $in = $this->tx($this->deposit, 500, '2026-10-06', $this->checking->iban);

        $this->link($out);

        $this->assertNull($in->fresh()->transfer_group_id);
        $this->assertSame(TransactionKind::Income, $in->fresh()->kind);
    }

    public function test_a_counterpart_with_another_amount_is_not_linked(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);
        $in = $this->tx($this->deposit, 450, '2026-10-02', $this->checking->iban);

        $this->link($out);

        $this->assertNull($in->fresh()->transfer_group_id);
    }

    public function test_the_closest_date_wins_when_two_counterparts_match(): void
    {
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);
        $far = $this->tx($this->deposit, 500, '2026-10-04', $this->checking->iban);
        $near = $this->tx($this->deposit, 500, '2026-10-02', $this->checking->iban);

        $this->link($out);

        $this->assertNotNull($near->fresh()->transfer_group_id);
        $this->assertNull($far->fresh()->transfer_group_id);
    }

    public function test_a_counterpart_that_already_belongs_to_a_group_is_left_alone(): void
    {
        $group = (string) Str::ulid();
        $taken = $this->tx($this->deposit, 500, '2026-10-02', $this->checking->iban, [
            'transfer_group_id' => $group,
        ]);
        $out = $this->tx($this->checking, -500, '2026-10-01', $this->deposit->iban);

        $this->link($out);

        $this->assertSame($group, $taken->fresh()->transfer_group_id);
        $this->assertNull($out->fresh()->transfer_group_id);
    }

    public function test_a_payment_to_someone_elses_account_stays_an_expense(): void
    {
        $out = $this->tx($this->checking, -25, '2026-10-01', 'NL00XXXX0000000000');

        $this->link($out);

        $this->assertSame(TransactionKind::Expense, $out->fresh()->kind);
    }

    public function test_an_account_cannot_be_a_transfer_to_itself(): void
    {
        $row = $this->tx($this->checking, -10, '2026-10-01', $this->checking->iban);

        $this->link($row);

        $this->assertSame(TransactionKind::Expense, $row->fresh()->kind);
    }

    public function test_a_row_without_a_counterparty_iban_is_ignored(): void
    {
        $row = $this->tx($this->checking, -10, '2026-10-01', null);

        $this->link($row);

        $this->assertSame(TransactionKind::Expense, $row->fresh()->kind);
    }

    public function test_accounts_of_other_users_are_never_linked(): void
    {
        $stranger = AccountFactory::new()->create(['iban' => 'NL55INGB0001112223']);
        $out = $this->tx($this->checking, -500, '2026-10-01', $stranger->iban);

        $this->link($out);

        // The IBAN is not one of this user's own accounts.
        $this->assertSame(TransactionKind::Expense, $out->fresh()->kind);
    }

    public function test_a_transfer_partner_of_a_plain_transaction_is_null(): void
    {
        $row = $this->tx($this->checking, -10, '2026-10-01');

        $this->assertNull($row->transferPartner());
    }
}
