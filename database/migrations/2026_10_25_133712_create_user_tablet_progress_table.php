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
        Schema::create('user_tablet_progress', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('tablet_id')->constrained('tablets_of_wisdom')->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // A user can unlock the same tablet only once.
            $table->unique(['user_id', 'tablet_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tablet_progress');
    }
};
