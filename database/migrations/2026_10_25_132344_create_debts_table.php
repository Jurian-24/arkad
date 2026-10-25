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
        Schema::create('debts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->nullOnDelete();
            $table->foreignUlid('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('target_amount');
            $table->char('currency', 3)->default('EUR');
            $table->date('target_date')->nullable();
            $table->string('status'); // enum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
