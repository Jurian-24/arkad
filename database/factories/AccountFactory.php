<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'institution_id' => InstitutionFactory::new(),
            'name' => fake()->words(2, true),
            'type' => AccountType::Checking,
            'iban' => fake()->unique()->iban('NL'),
            'currency' => 'EUR',
            'opened_at' => '2025-01-01',
        ];
    }

    public function deposit(): static
    {
        return $this->state(['type' => AccountType::Deposit]);
    }

    public function savings(): static
    {
        return $this->state(['type' => AccountType::Savings]);
    }

    public function investment(): static
    {
        return $this->state(['type' => AccountType::Investment]);
    }
}
