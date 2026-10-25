<?php

namespace Database\Factories;

use App\Enums\TransactionKind;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'account_id' => AccountFactory::new(),
            // Same owner as the account, unless a test says otherwise.
            'user_id' => fn (array $attributes) => Account::find($attributes['account_id'])->user_id,
            'booked_at' => '2026-10-01',
            'amount' => -10,
            'currency' => 'EUR',
            'kind' => TransactionKind::Expense,
            'description' => fake()->sentence(3),
        ];
    }

    public function income(float $amount = 1000): static
    {
        return $this->state(['kind' => TransactionKind::Income, 'amount' => abs($amount)]);
    }

    public function expense(float $amount = 10): static
    {
        return $this->state(['kind' => TransactionKind::Expense, 'amount' => -abs($amount)]);
    }
}
