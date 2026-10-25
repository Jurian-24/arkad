<?php

namespace Tests\Feature;

use App\Enums\RawRowStatus;
use App\Models\Account;
use App\Models\RawBankTransaction;
use Database\Factories\AccountFactory;
use Database\Factories\ImportBatchFactory;
use Database\Factories\RawBankTransactionFactory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDedupeTest extends TestCase
{
    use RefreshDatabase;

    private function rawRow(Account $account, string $hash)
    {
        $batch = ImportBatchFactory::new()->create(['account_id' => $account->id]);

        return RawBankTransactionFactory::new()->create([
            'import_batch_id' => $batch->id,
            'dedupe_hash' => $hash,
        ]);
    }

    public function test_the_same_row_cannot_be_imported_twice_for_one_account(): void
    {
        $account = AccountFactory::new()->create();
        $this->rawRow($account, 'same-hash');

        $this->expectException(QueryException::class);

        // A second upload of an overlapping CSV, so a different batch.
        $this->rawRow($account, 'same-hash');
    }

    public function test_the_same_hash_is_fine_on_a_different_account(): void
    {
        $this->rawRow(AccountFactory::new()->create(), 'same-hash');
        $this->rawRow(AccountFactory::new()->create(), 'same-hash');

        $this->assertSame(2, RawBankTransaction::where('dedupe_hash', 'same-hash')->count());
    }

    public function test_different_hashes_on_one_account_are_fine(): void
    {
        $account = AccountFactory::new()->create();

        $this->rawRow($account, 'hash-1');
        $this->rawRow($account, 'hash-2');

        $this->assertSame(2, RawBankTransaction::where('account_id', $account->id)->count());
    }

    public function test_the_original_csv_row_survives_a_round_trip_untouched(): void
    {
        $row = $this->rawRow(AccountFactory::new()->create(), 'h');
        $row->update(['payload' => ['Datum' => '20261001', 'Naam / Omschrijving' => 'Albert Heijn', 'Bedrag (EUR)' => '-12,50']]);

        $fresh = $row->fresh();

        $this->assertSame('-12,50', $fresh->payload['Bedrag (EUR)']);
        $this->assertSame('Albert Heijn', $fresh->payload['Naam / Omschrijving']);
    }

    public function test_the_payload_is_not_exposed_when_a_row_is_serialized(): void
    {
        $row = $this->rawRow(AccountFactory::new()->create(), 'h');

        $this->assertArrayNotHasKey('payload', $row->toArray());
    }

    public function test_the_row_status_is_cast_to_an_enum(): void
    {
        $row = $this->rawRow(AccountFactory::new()->create(), 'h');

        $this->assertSame(RawRowStatus::Parsed, $row->fresh()->status);
    }

    public function test_deleting_a_batch_removes_its_raw_rows(): void
    {
        $account = AccountFactory::new()->create();
        $row = $this->rawRow($account, 'h');

        $row->importBatch->delete();

        $this->assertSame(0, RawBankTransaction::count());
    }
}
