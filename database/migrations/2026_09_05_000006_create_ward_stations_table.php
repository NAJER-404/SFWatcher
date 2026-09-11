<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ward_stations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. WARD-SF-01
            $table->string('name');
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->enum('status', ['active', 'degraded', 'breached'])->default('active')->index();
            $table->unsignedTinyInteger('shield_level')->default(100); // 0-100%
            $table->unsignedTinyInteger('energy_level')->default(100); // 0-100%
            $table->string('frequency')->default('432.8 THz');
            $table->unsignedInteger('radius_meters')->default(1000);
            $table->date('last_recalibrated')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ward_stations');
    }
};
