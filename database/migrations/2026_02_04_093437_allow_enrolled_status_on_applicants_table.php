<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE applicants MODIFY status ENUM('applied','admitted','rejected','enrolled') NOT NULL DEFAULT 'applied'"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('applicants')
                ->where('status', 'enrolled')
                ->update(['status' => 'admitted']);

            DB::statement(
                "ALTER TABLE applicants MODIFY status ENUM('applied','admitted','rejected') NOT NULL DEFAULT 'applied'"
            );
        }
    }
};
