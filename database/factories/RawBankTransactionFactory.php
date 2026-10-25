<?php

namespace Database\Factories;

use App\Enums\RawRowStatus;
use App\Models\ImportBatch;
use App\Models\RawBankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class RawBankTransactionFactory extends Factory
{
    protected $model = RawBankTransaction::class;

    public function definition(): array
    {
        return [
            'import_batch_id' => ImportBatchFactory::new(),
            'user_id' => fn (array $a) => ImportBatch::find($a['import_batch_id'])->user_id,
            'account_id' => fn (array $a) => ImportBatch::find($a['import_batch_id'])->account_id,
            'row_number' => 1,
            'payload' => ['Datum' => '20261001', 'Bedrag' => '-12,50'],
            'dedupe_hash' => fake()->unique()->sha256(),
            'status' => RawRowStatus::Parsed,
        ];
    }
}
