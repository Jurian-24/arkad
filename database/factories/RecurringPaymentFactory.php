<?php

namespace Database\Factories;

use App\Enums\Frequency;
use App\Enums\RecurringKind;
use App\Enums\RecurringSource;
use App\Enums\RecurringStatus;
use App\Models\Account;
use App\Models\RecurringPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringPaymentFactory extends Factory
{
    protected $model = RecurringPayment::class;

    public function definition(): array
    {
        return [
            'account_id' => AccountFactory::new(),
            'user_id' => fn (array $attributes) => Account::find($attributes['account_id'])->user_id,
            'name' => fake()->words(2, true),
            'kind' => RecurringKind::Expense,
            'amount' => -9.99,
            'currency' => 'EUR',
            'amount_is_variable' => false,
            'frequency' => Frequency::Monthly,
            'interval_count' => 1,
            'anchor_date' => '2026-01-15',
            'status' => RecurringStatus::Active,
            'source' => RecurringSource::Manual,
        ];
    }
}
