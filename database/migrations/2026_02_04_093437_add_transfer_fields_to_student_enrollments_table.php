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
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->nullable();
            $table->date('transferred_in_at')->nullable();
            $table->date('transferred_out_at')->nullable();
            $table->string('transfer_reason', 500)->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'enrollment_date',
                'transferred_in_at',
                'transferred_out_at',
                'transfer_reason',
                'notes',
            ]);
        });
    }
};
