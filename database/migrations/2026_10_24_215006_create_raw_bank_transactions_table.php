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
        Schema::create('raw_bank_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('payload');
            $table->string('dedupe_hash');
            $table->string('status')->default('parsed'); // parsed, error
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'dedupe_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_bank_transactions');
    }
};
