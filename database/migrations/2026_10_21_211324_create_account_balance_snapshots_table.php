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
        Schema::create('account_balance_snapshots', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->date('as_of_date');
            $table->decimal('balance', 14, 2);
            $table->decimal('contributions', 14, 2)->default(0);
            $table->string('source')->default('manual'); // manual, derived, import
            $table->timestamps();

            $table->unique(['account_id', 'as_of_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_balance_snapshots');
    }
};
