<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., Term 1, Term 2, Term 3
            $table->unsignedTinyInteger('sequence')->default(1); // 1..n ordering within year
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['academic_year_id', 'name']);
            $table->index(['school_id', 'academic_year_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
