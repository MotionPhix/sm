<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., Secondary Default
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('grade_scale_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_scale_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('min_percent'); // inclusive
            $table->unsignedTinyInteger('max_percent'); // inclusive
            $table->string('grade', 4); // e.g., A+, A, B, C, D, E, F
            $table->string('comment')->nullable(); // e.g., Excellent, Good, etc.
            $table->unsignedTinyInteger('ordering')->default(1);
            $table->timestamps();

            $table->unique(['grade_scale_id', 'grade']);
            $table->index(['grade_scale_id', 'ordering']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scale_steps');
        Schema::dropIfExists('grade_scales');
    }
};
