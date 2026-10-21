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
            $table->ulid('account_id');
            $table->date('as_of_date');
            $table->decimal('balance');
            $table->decimal('contributions');
            $table->string('source');
            $table->timestamps();
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
