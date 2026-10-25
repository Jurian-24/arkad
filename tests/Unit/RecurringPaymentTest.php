<?php

namespace Tests\Unit;

use App\Models\RecurringPayment;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecurringPaymentTest extends TestCase
{
    private function payment(array $attributes): RecurringPayment
    {
        return new RecurringPayment($attributes + [
            'frequency' => 'monthly',
            'interval_count' => 1,
            'amount' => 10,
            'anchor_date' => '2026-01-15',
        ]);
    }

    // ------------------------------------------------ monthly equivalent

    public function test_monthly_payment_is_its_own_monthly_equivalent(): void
    {
        $this->assertEquals(10.00, $this->payment(['amount' => 10])->monthly_equivalent);
    }

    public function test_yearly_payment_is_divided_by_twelve(): void
    {
        $payment = $this->payment(['frequency' => 'yearly', 'amount' => 240]);

        $this->assertEquals(20.00, $payment->monthly_equivalent);
    }

    public function test_quarterly_payment_is_divided_by_three(): void
    {
        $payment = $this->payment(['frequency' => 'quarterly', 'amount' => 60]);

        $this->assertEquals(20.00, $payment->monthly_equivalent);
    }

    public function test_every_two_weeks_is_26_payments_a_year(): void
    {
        $payment = $this->payment(['frequency' => 'weekly', 'interval_count' => 2, 'amount' => 100]);

        $this->assertEquals(216.67, $payment->monthly_equivalent);
    }

    public function test_every_two_months_halves_the_monthly_cost(): void
    {
        $payment = $this->payment(['interval_count' => 2, 'amount' => 30]);

        $this->assertEquals(15.00, $payment->monthly_equivalent);
    }

    // ------------------------------------------------ next due date

    public function test_next_due_date_is_the_anchor_when_it_is_still_ahead(): void
    {
        $payment = $this->payment(['anchor_date' => '2026-03-10']);

        $next = $payment->nextDueDate(Carbon::parse('2026-01-01'));

        $this->assertSame('2026-03-10', $next->toDateString());
    }

    public function test_a_payment_due_today_is_due_today(): void
    {
        $payment = $this->payment(['anchor_date' => '2026-01-15']);

        $next = $payment->nextDueDate(Carbon::parse('2026-03-15'));

        $this->assertSame('2026-03-15', $next->toDateString());
    }

    public function test_next_due_date_moves_to_the_following_month(): void
    {
        $payment = $this->payment(['anchor_date' => '2026-01-15']);

        $next = $payment->nextDueDate(Carbon::parse('2026-03-16'));

        $this->assertSame('2026-04-15', $next->toDateString());
    }

    public function test_a_payment_on_the_31st_does_not_drift_to_the_28th(): void
    {
        $payment = $this->payment(['anchor_date' => '2026-01-31']);

        $this->assertSame('2026-02-28', $payment->nextDueDate(Carbon::parse('2026-02-01'))->toDateString());
        $this->assertSame('2026-03-31', $payment->nextDueDate(Carbon::parse('2026-03-01'))->toDateString());
        $this->assertSame('2026-04-30', $payment->nextDueDate(Carbon::parse('2026-04-01'))->toDateString());
    }

    public function test_every_two_weeks(): void
    {
        $payment = $this->payment([
            'frequency' => 'weekly',
            'interval_count' => 2,
            'anchor_date' => '2026-01-05',
        ]);

        $next = $payment->nextDueDate(Carbon::parse('2026-01-06'));

        $this->assertSame('2026-01-19', $next->toDateString());
    }

    public function test_there_is_no_next_date_after_the_end_date(): void
    {
        $payment = $this->payment([
            'anchor_date' => '2026-01-31',
            'ends_on' => '2026-02-15',
        ]);

        $this->assertNull($payment->nextDueDate(Carbon::parse('2026-02-01')));
    }

    public function test_the_end_date_itself_still_counts(): void
    {
        $payment = $this->payment([
            'anchor_date' => '2026-01-15',
            'ends_on' => '2026-02-15',
        ]);

        $next = $payment->nextDueDate(Carbon::parse('2026-02-01'));

        $this->assertSame('2026-02-15', $next->toDateString());
    }
}
