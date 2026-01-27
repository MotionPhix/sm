<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., Midterm Test, Coursework, Final Exam
            $table->unsignedSmallInteger('ordering')->default(1);
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->decimal('weight', 5, 2)->default(0); // % toward term total per subject
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['term_id', 'subject_id', 'name']);
            $table->index(['school_id', 'academic_year_id', 'term_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_plans');
    }
};
