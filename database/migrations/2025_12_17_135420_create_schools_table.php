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
            $table->uuid('uuid')->unique()->nullable();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('type')->default('primary'); // primary, secondary, private, etc
            
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            $table->string('district')->nullable();
            $table->string('country')->default('Malawi');

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
