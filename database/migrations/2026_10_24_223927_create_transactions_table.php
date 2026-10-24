<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('raw_transaction_id')->nullable()->constrained('raw_bank_transactions')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('recurring_payment')->nullable()->constrained()->cascadeOnDelete();
            $table->date('booked_at');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('EUR');
            $table->string('kind'); // enum
            $table->text('description');
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_iban')->nullable();
            $table->ulid('transfer_group_id')->nullable()->index(); // a transfer within a account from another account
            $table->string('external_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'booked_at']);
            $table->index(['account_id', 'booked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
