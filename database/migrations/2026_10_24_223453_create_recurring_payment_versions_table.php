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
        Schema::create('recurring_payment_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('recurring_payment_id')->constrained('recurring_payments')->cascadeOnDelete();
            $table->decimal('amount');
            $table->date('valid_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_payment_versions');
    }
};
