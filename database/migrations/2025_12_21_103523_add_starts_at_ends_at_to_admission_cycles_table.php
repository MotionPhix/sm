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
        Schema::table('admission_cycles', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('target_class')->nullable();
            $table->unsignedInteger('max_intake')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_cycles', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at', 'target_class', 'max_intake']);
        });
    }
};
