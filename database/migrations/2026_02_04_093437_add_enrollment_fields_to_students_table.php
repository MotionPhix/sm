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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('applicant_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('current_class_stream_assignment_id')
                ->nullable()
                ->constrained('class_stream_assignments')
                ->nullOnDelete();
            $table->date('withdrawn_at')->nullable();
            $table->string('withdrawal_reason', 500)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['withdrawn_at', 'withdrawal_reason']);
            $table->dropConstrainedForeignId('current_class_stream_assignment_id');
            $table->dropConstrainedForeignId('applicant_id');
        });
    }
};
