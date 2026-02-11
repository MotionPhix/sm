<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_stream_assignment_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('entered_at');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'assessment_plan_id'], 'student_assessment_unique');
            $table->index(['school_id', 'assessment_plan_id']);
            $table->index(['school_id', 'class_stream_assignment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
