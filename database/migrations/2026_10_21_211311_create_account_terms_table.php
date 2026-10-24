<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_terms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('principal', 14, 2);
            $table->decimal('interest_rate_pct', 6, 3);
            $table->date('start_date');
            $table->date('maturity_date')->nullable();
            $table->string('compounding')->default('none'); // none, monthly, yearly
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_terms');
    }
};
