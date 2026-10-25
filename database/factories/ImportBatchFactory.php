<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Account;
use App\Models\ImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportBatchFactory extends Factory
{
    protected $model = ImportBatch::class;

    public function definition(): array
    {
        return [
            'account_id' => AccountFactory::new(),
            'user_id' => fn (array $attributes) => Account::find($attributes['account_id'])->user_id,
            'filename' => 'transactions.csv',
            'file_hash' => fake()->sha256(),
            'status' => ImportStatus::Pending,
            'rows_total' => 0,
            'rows_imported' => 0,
            'rows_skipped' => 0,
        ];
    }

    public function processed(): static
    {
        return $this->state(['status' => ImportStatus::Processed, 'imported_at' => now()]);
    }
}
