<?php

namespace Tests\Unit;

use App\Enums\Frequency;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class FrequencyTest extends TestCase
{
    public function test_periods_per_year(): void
    {
        $this->assertEquals(52, Frequency::Weekly->periodsPerYear());
        $this->assertEquals(12, Frequency::Monthly->periodsPerYear());
        $this->assertEquals(4, Frequency::Quarterly->periodsPerYear());
        $this->assertEquals(1, Frequency::Yearly->periodsPerYear());
    }

    public function test_monthly_advance_does_not_overflow_into_the_next_month(): void
    {
        $date = Frequency::Monthly->advance(Carbon::parse('2026-01-31'), 1);

        $this->assertSame('2026-02-28', $date->toDateString());
    }

    public function test_advancing_from_the_anchor_returns_to_the_31st(): void
    {
        // Two months after Jan 31 is Mar 31, not Mar 28.
        $date = Frequency::Monthly->advance(Carbon::parse('2026-01-31'), 2);

        $this->assertSame('2026-03-31', $date->toDateString());
    }

    public function test_quarterly_advance(): void
    {
        $date = Frequency::Quarterly->advance(Carbon::parse('2026-01-31'), 1);

        $this->assertSame('2026-04-30', $date->toDateString());
    }

    public function test_yearly_advance_from_a_leap_day(): void
    {
        $date = Frequency::Yearly->advance(Carbon::parse('2028-02-29'), 1);

        $this->assertSame('2029-02-28', $date->toDateString());
    }

    public function test_weekly_advance_with_a_count(): void
    {
        $date = Frequency::Weekly->advance(Carbon::parse('2026-01-05'), 2);

        $this->assertSame('2026-01-19', $date->toDateString());
    }
}
