<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->index(); // 'Ward Devices', 'Containment Units', 'Spectral Equipment', 'Ghost-Hunting Gadgets'
            $table->decimal('price', 10, 2)->default(0.00);
            $table->unsignedInteger('stock')->default(0);
            $table->enum('status', ['in_stock', 'low_stock', 'out_of_stock', 'restricted'])->default('in_stock')->index();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
