<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('investigator_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes');
            $table->dateTime('investigation_date')->index();
            $table->string('result')->nullable(); // 'VERIFIED', 'RESOLVED', 'ESCALATED', etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
