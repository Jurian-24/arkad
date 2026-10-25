<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id');
            $table->foreignUlid('category_id');
            $table->string('match_field'); //enum
            $table->string('match_type'); // enum
            $table->string('match_value');
            $table->integer('priority');
            $table->timestamps();
        });

        DB::statement('
            ALTER TABLE category_rules
            ADD CONSTRAINT priority CHECK (priority > 0 AND priority <= 5)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_rules');
    }
};
