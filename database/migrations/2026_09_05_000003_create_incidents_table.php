<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code')->unique()->nullable(); // e.g. SF-INC-001
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->string('incident_type')->index(); // 'Ectoplasmic Anomaly', 'Spirit Activity', 'Spectral Residue', 'Ward Failure', 'Containment Breach', 'Unknown Phenomenon'
            $table->string('title');
            $table->text('description');
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->dateTime('incident_date')->index();
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'EXTREME', 'ESCALATED', 'CRITICAL'])->default('MEDIUM')->index();
            $table->enum('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED', 'RESOLVED', 'ESCALATED'])->default('PENDING')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
