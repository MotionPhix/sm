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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $t->uuid('uuid')->unique();
            $t->string('name');
            $t->string('code')->nullable();
            
            table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();

            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
