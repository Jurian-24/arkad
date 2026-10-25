<?php

namespace Tests\Unit;

use App\Models\AccountTerm;
use Tests\TestCase;

class AccountTermTest extends TestCase
{
    private function term(array $attributes): AccountTerm
    {
        return new AccountTerm($attributes + [
            'principal' => 1000,
            'interest_rate_pct' => 3,
            'start_date' => '2026-01-01',
            'maturity_date' => '2027-01-01', // 365 days
            'compounding' => 'none',
        ]);
    }

    public function test_simple_interest_over_one_year(): void
    {
        $this->assertEqualsWithDelta(1030.00, $this->term([])->projectedMaturityValue(), 0.01);
    }

    public function test_yearly_compounding_over_one_year_equals_simple_interest(): void
    {
        $term = $this->term(['compounding' => 'yearly']);

        $this->assertEqualsWithDelta(1030.00, $term->projectedMaturityValue(), 0.01);
    }

    public function test_monthly_compounding_earns_slightly_more(): void
    {
        $term = $this->term(['compounding' => 'monthly']);

        $this->assertEqualsWithDelta(1030.42, $term->projectedMaturityValue(), 0.01);
    }

    public function test_an_open_ended_account_has_no_projection_and_never_matures(): void
    {
        $term = $this->term(['maturity_date' => null]);

        $this->assertNull($term->projectedMaturityValue());
        $this->assertNull($term->daysUntilMaturity());
        $this->assertFalse($term->hasMatured());
    }

    public function test_has_matured(): void
    {
        $this->assertTrue($this->term(['maturity_date' => '2020-01-01'])->hasMatured());
        $this->assertFalse($this->term(['maturity_date' => '2999-01-01'])->hasMatured());
    }
}
