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
        Schema::create('recurring_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('destination_account_id')->nullable()
                ->constrained('accounts')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('kind'); // expense, income, internal_transfer
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('EUR');
            $table->boolean('amount_is_variable')->default(false);
            $table->string('frequency'); // weekly, monthly, quarterly, yearly
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->date('anchor_date');
            $table->date('ends_on')->nullable();
            $table->string('status')->default('active'); // suggested, active, paused, ended
            $table->string('source')->default('manual'); // manual, detected
            $table->json('match_pattern')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_payments');
    }
};
