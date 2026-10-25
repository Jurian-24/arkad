<?php

namespace Tests\Feature;

use App\Enums\Bucket;
use App\Models\AllocationRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AllocationRuleTest extends TestCase
{
    use RefreshDatabase;

    private function rule(User $user, Bucket $bucket, float $pct, string $from): void
    {
        $user->allocationRules()->create([
            'bucket' => $bucket,
            'target_pct' => $pct,
            'valid_from' => $from,
        ]);
    }

    public function test_a_70_20_10_split_fits_in_the_column(): void
    {
        $user = User::factory()->create();

        $this->rule($user, Bucket::Living, 70, '2025-01-01');
        $this->rule($user, Bucket::Debt, 20, '2025-01-01');
        $this->rule($user, Bucket::Savings, 10, '2025-01-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-01-01'));

        $this->assertEquals(100, $rules->sum(fn ($r) => (float) $r->target_pct));
    }

    public function test_the_newest_rule_that_has_started_wins(): void
    {
        $user = User::factory()->create();

        $this->rule($user, Bucket::Living, 70, '2025-01-01');
        $this->rule($user, Bucket::Living, 60, '2026-06-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-10-01'));

        $this->assertEquals(60, (float) $rules['living']->target_pct);
    }

    public function test_an_older_month_still_uses_the_older_split(): void
    {
        $user = User::factory()->create();

        $this->rule($user, Bucket::Living, 70, '2025-01-01');
        $this->rule($user, Bucket::Living, 60, '2026-06-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-01-01'));

        $this->assertEquals(70, (float) $rules['living']->target_pct);
    }

    public function test_a_rule_that_starts_in_the_future_is_ignored(): void
    {
        $user = User::factory()->create();

        $this->rule($user, Bucket::Savings, 10, '2025-01-01');
        $this->rule($user, Bucket::Savings, 20, '2027-01-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-10-01'));

        $this->assertEquals(10, (float) $rules['savings']->target_pct);
    }

    public function test_a_rule_on_its_start_date_already_applies(): void
    {
        $user = User::factory()->create();

        $this->rule($user, Bucket::Savings, 15, '2026-10-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-10-01'));

        $this->assertEquals(15, (float) $rules['savings']->target_pct);
    }

    public function test_rules_of_other_users_are_ignored(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->rule($user, Bucket::Savings, 10, '2025-01-01');
        $this->rule($other, Bucket::Savings, 50, '2026-01-01');

        $rules = AllocationRule::effectiveFor($user, Carbon::parse('2026-10-01'));

        $this->assertEquals(10, (float) $rules['savings']->target_pct);
    }

    public function test_only_one_rule_per_bucket_per_start_date(): void
    {
        $user = User::factory()->create();
        $this->rule($user, Bucket::Savings, 10, '2025-01-01');

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->rule($user, Bucket::Savings, 12, '2025-01-01');
    }
}
