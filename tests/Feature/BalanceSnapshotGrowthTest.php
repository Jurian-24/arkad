<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountBalanceSnapshot;
use Database\Factories\AccountBalanceSnapshotFactory;
use Database\Factories\AccountFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceSnapshotGrowthTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = AccountFactory::new()->investment()->create();
    }

    private function snapshot(string $date, float $balance, float $contributions = 0, ?Account $account = null): AccountBalanceSnapshot
    {
        return AccountBalanceSnapshotFactory::new()->create([
            'account_id' => ($account ?? $this->account)->id,
            'as_of_date' => $date,
            'balance' => $balance,
            'contributions' => $contributions,
        ]);
    }

    public function test_the_first_snapshot_has_no_growth(): void
    {
        $first = $this->snapshot('2026-01-31', 1000, 1000);

        $this->assertNull($first->growth());
    }

    public function test_money_put_in_is_not_counted_as_growth(): void
    {
        $this->snapshot('2026-01-31', 1000, 1000);
        $second = $this->snapshot('2026-02-28', 2040, 1000);

        // 2040 - 1000 - 1000 = 40
        $this->assertEquals(40.0, $second->growth());
    }

    public function test_a_deposit_alone_is_zero_growth(): void
    {
        $this->snapshot('2026-01-31', 1000, 1000);
        $second = $this->snapshot('2026-02-28', 1500, 500);

        $this->assertEquals(0.0, $second->growth());
    }

    public function test_a_loss_is_negative_growth(): void
    {
        $this->snapshot('2026-01-31', 1000, 1000);
        $second = $this->snapshot('2026-02-28', 950);

        $this->assertEquals(-50.0, $second->growth());
    }

    public function test_a_withdrawal_is_not_counted_as_a_loss(): void
    {
        $this->snapshot('2026-01-31', 1000, 1000);
        $second = $this->snapshot('2026-02-28', 700, -300);

        $this->assertEquals(0.0, $second->growth());
    }

    public function test_growth_is_measured_against_the_snapshot_right_before_it(): void
    {
        $this->snapshot('2026-01-31', 1000, 1000);
        $this->snapshot('2026-02-28', 1100);
        $third = $this->snapshot('2026-03-31', 1155);

        // 1155 - 1100, not 1155 - 1000
        $this->assertEquals(55.0, $third->growth());
    }

    public function test_snapshots_of_other_accounts_are_ignored(): void
    {
        $other = AccountFactory::new()->investment()->create();

        $this->snapshot('2026-01-31', 1000, 1000);
        $this->snapshot('2026-02-15', 99999, 0, $other);
        $second = $this->snapshot('2026-02-28', 1010);

        $this->assertEquals(10.0, $second->growth());
    }

    public function test_the_latest_snapshot_of_an_account_is_found_by_date_not_by_insert_order(): void
    {
        $this->snapshot('2026-03-31', 1300);
        $this->snapshot('2026-01-31', 1000);
        $this->snapshot('2026-02-28', 1100);

        $this->assertEquals(1300, (float) $this->account->fresh()->latestSnapshot->balance);
    }

    public function test_only_one_snapshot_per_account_per_date(): void
    {
        $this->snapshot('2026-01-31', 1000);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->snapshot('2026-01-31', 2000);
    }
}
