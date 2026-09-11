<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('resource_type')->index(); // 'Ectoplasmic Reservoir', 'Spirit Residue Node', 'Soul Anchor Deposit'
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('units'); // 'L/hr', 'kg', 'units'
            $table->string('purity')->nullable()->default('90%');
            $table->string('yield_rate')->nullable();
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->enum('status', ['available', 'depleted', 'restricted'])->default('available')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
