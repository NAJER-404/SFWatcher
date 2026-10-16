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
        Schema::table('ward_stations', function (Blueprint $table) {
            if (!Schema::hasColumn('ward_stations', 'description')) {
                $table->string('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('ward_stations', 'is_safe_zone')) {
                $table->boolean('is_safe_zone')->default(false)->after('status')->index();
            }
            if (!Schema::hasColumn('ward_stations', 'safe_zone_radius_meters')) {
                $table->unsignedInteger('safe_zone_radius_meters')->nullable()->after('is_safe_zone');
            }
            if (!Schema::hasColumn('ward_stations', 'ward_protection_expires_at')) {
                $table->timestamp('ward_protection_expires_at')->nullable()->after('safe_zone_radius_meters');
            }
            if (!Schema::hasColumn('ward_stations', 'relief_food')) {
                $table->string('relief_food')->default('available')->after('ward_protection_expires_at');
            }
            if (!Schema::hasColumn('ward_stations', 'relief_water')) {
                $table->string('relief_water')->default('available')->after('relief_food');
            }
            if (!Schema::hasColumn('ward_stations', 'relief_first_aid')) {
                $table->string('relief_first_aid')->default('available')->after('relief_water');
            }
            if (!Schema::hasColumn('ward_stations', 'assistance_services')) {
                $table->json('assistance_services')->nullable()->after('relief_first_aid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ward_stations', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'description',
                'is_safe_zone',
                'safe_zone_radius_meters',
                'ward_protection_expires_at',
                'relief_food',
                'relief_water',
                'relief_first_aid',
                'assistance_services'
            ] as $col) {
                if (Schema::hasColumn('ward_stations', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
