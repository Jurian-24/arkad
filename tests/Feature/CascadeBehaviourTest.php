<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountBalanceSnapshot;
use App\Models\Category;
use App\Models\Institution;
use App\Models\RecurringPayment;
use App\Models\Transaction;
use App\Models\User;
use Database\Factories\AccountBalanceSnapshotFactory;
use Database\Factories\AccountFactory;
use Database\Factories\CategoryFactory;
use Database\Factories\ImportBatchFactory;
use Database\Factories\InstitutionFactory;
use Database\Factories\RawBankTransactionFactory;
use Database\Factories\RecurringPaymentFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * What happens to related rows when something is deleted. These protect the
 * user's data: removing a category must never remove their transactions.
 */
class CascadeBehaviourTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_category_keeps_its_transactions(): void
    {
        $account = AccountFactory::new()->create();
        $category = CategoryFactory::new()->create(['user_id' => $account->user_id]);
        $tx = TransactionFactory::new()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);

        $category->delete();

        $this->assertNotNull(Transaction::find($tx->id));
        $this->assertNull($tx->fresh()->category_id);
    }

    public function test_deleting_a_recurring_payment_keeps_its_transactions(): void
    {
        $account = AccountFactory::new()->create();
        $payment = RecurringPaymentFactory::new()->create(['account_id' => $account->id]);
        $tx = TransactionFactory::new()->create([
            'account_id' => $account->id,
            'recurring_payment_id' => $payment->id,
        ]);

        $payment->delete();

        $this->assertNotNull(Transaction::find($tx->id));
        $this->assertNull($tx->fresh()->recurring_payment_id);
    }

    public function test_deleting_a_category_keeps_the_recurring_payments_in_it(): void
    {
        $account = AccountFactory::new()->create();
        $category = CategoryFactory::new()->create(['user_id' => $account->user_id]);
        $payment = RecurringPaymentFactory::new()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);

        $category->delete();

        $this->assertNotNull(RecurringPayment::find($payment->id));
        $this->assertNull($payment->fresh()->category_id);
    }

    public function test_deleting_a_raw_row_keeps_the_normalized_transaction(): void
    {
        $account = AccountFactory::new()->create();
        $batch = ImportBatchFactory::new()->create(['account_id' => $account->id]);
        $raw = RawBankTransactionFactory::new()->create(['import_batch_id' => $batch->id]);
        $tx = TransactionFactory::new()->create([
            'account_id' => $account->id,
            'raw_transaction_id' => $raw->id,
        ]);

        $raw->delete();

        $this->assertNotNull(Transaction::find($tx->id));
        $this->assertNull($tx->fresh()->raw_transaction_id);
    }

    public function test_deleting_an_account_removes_its_transactions_and_snapshots(): void
    {
        $account = AccountFactory::new()->create();
        TransactionFactory::new()->create(['account_id' => $account->id]);
        AccountBalanceSnapshotFactory::new()->create(['account_id' => $account->id]);

        $account->delete();

        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, AccountBalanceSnapshot::count());
    }

    public function test_deleting_a_user_removes_all_of_their_data_but_not_the_institution(): void
    {
        $user = User::factory()->create();
        $account = AccountFactory::new()->create(['user_id' => $user->id]);
        TransactionFactory::new()->create(['account_id' => $account->id]);
        CategoryFactory::new()->create(['user_id' => $user->id]);
        RecurringPaymentFactory::new()->create(['account_id' => $account->id]);

        $user->delete();

        $this->assertSame(0, Account::count());
        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, Category::count());
        $this->assertSame(0, RecurringPayment::count());
        $this->assertSame(1, Institution::count());
    }

    public function test_an_institution_with_accounts_cannot_be_deleted(): void
    {
        $institution = InstitutionFactory::new()->create();
        AccountFactory::new()->create(['institution_id' => $institution->id]);

        $this->expectException(QueryException::class);

        $institution->delete();
    }

    public function test_a_transaction_cannot_point_to_an_account_that_does_not_exist(): void
    {
        $this->expectException(QueryException::class);

        TransactionFactory::new()->create([
            'account_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'user_id' => User::factory()->create()->id,
        ]);
    }
}
