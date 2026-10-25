<?php

namespace Tests\Unit;

use App\Enums\RecurringStatus;
use PHPUnit\Framework\TestCase;

class RecurringStatusTest extends TestCase
{
    public function test_only_active_payments_count_in_the_budget(): void
    {
        $this->assertTrue(RecurringStatus::Active->countsInBudget());
        $this->assertFalse(RecurringStatus::Suggested->countsInBudget());
        $this->assertFalse(RecurringStatus::Paused->countsInBudget());
        $this->assertFalse(RecurringStatus::Ended->countsInBudget());
    }

    public function test_a_suggestion_can_be_confirmed_or_rejected(): void
    {
        $this->assertTrue(RecurringStatus::Suggested->canBecome(RecurringStatus::Active));
        $this->assertTrue(RecurringStatus::Suggested->canBecome(RecurringStatus::Ended));
        $this->assertFalse(RecurringStatus::Suggested->canBecome(RecurringStatus::Paused));
    }

    public function test_active_and_paused_can_switch_back_and_forth(): void
    {
        $this->assertTrue(RecurringStatus::Active->canBecome(RecurringStatus::Paused));
        $this->assertTrue(RecurringStatus::Paused->canBecome(RecurringStatus::Active));
    }

    public function test_an_ended_payment_cannot_come_back(): void
    {
        foreach (RecurringStatus::cases() as $status) {
            $this->assertFalse(RecurringStatus::Ended->canBecome($status));
        }

        $this->assertTrue(RecurringStatus::Ended->isFinal());
    }
}
