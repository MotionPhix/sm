<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('region');
            $table->string('district');
            $table->unique(['country', 'region', 'district']);
            $table->index(['country', 'region']);
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};
