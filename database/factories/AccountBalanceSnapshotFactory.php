<?php

namespace Database\Factories;

use App\Enums\SnapshotSource;
use App\Models\AccountBalanceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountBalanceSnapshotFactory extends Factory
{
    protected $model = AccountBalanceSnapshot::class;

    public function definition(): array
    {
        return [
            'account_id' => AccountFactory::new(),
            'as_of_date' => '2026-01-31',
            'balance' => 1000,
            'contributions' => 0,
            'source' => SnapshotSource::Manual,
        ];
    }
}
