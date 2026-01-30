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
        Schema::table('class_stream_assignments', function (Blueprint $table) {
            $table->integer('capacity')->default(40);
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_stream_assignments', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'is_active']);
        });
    }
};
