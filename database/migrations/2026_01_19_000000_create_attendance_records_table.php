<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            // Tenancy and academic scope
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            // Date of attendance
            $table->date('date');

            // Class/Stream context
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('stream_id')->nullable()->constrained()->nullOnDelete();

            // Student and status
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->index();
            $table->string('remarks', 500)->nullable();

            // Who recorded and when
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();

            $table->timestamps();

            // Ensure uniqueness per student per date within school/year
            $table->unique(['school_id', 'academic_year_id', 'date', 'student_id'], 'uniq_attendance_student_date');

            // Helpful composite index for listing
            $table->index(['school_id', 'academic_year_id', 'date', 'school_class_id', 'stream_id'], 'idx_attendance_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
