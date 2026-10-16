<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('libraries');

        // Clean up legacy migration records if present
        DB::table('migrations')->whereIn('migration', [
            '2026_07_09_133637_create_students_table',
            '2026_07_23_102916_create_libraries_table',
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation for unused legacy assignment tables
    }
};
