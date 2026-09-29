<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old check constraint on severity if in PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_severity_check');
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_severity_check CHECK (severity::text = ANY (ARRAY['LOW'::character varying, 'MEDIUM'::character varying, 'HIGH'::character varying, 'EXTREME'::character varying, 'ESCALATED'::character varying, 'CRITICAL'::character varying]::text[]))");
        }

        // 2. Add required_responder_class column
        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'required_responder_class')) {
                $table->string('required_responder_class')->nullable()->after('severity');
            }
        });

        // 3. Migrate any legacy CRITICAL values to EXTREME
        DB::table('incidents')->where('severity', 'CRITICAL')->update(['severity' => 'EXTREME']);

        // 4. Update default anomaly_max_hp and required_responder_class for existing incidents
        $hpMap = [
            'LOW' => [50, 'D'],
            'MEDIUM' => [80, 'D'],
            'HIGH' => [110, 'C'],
            'EXTREME' => [130, 'B'],
            'ESCALATED' => [150, 'A'],
        ];

        foreach ($hpMap as $sev => [$hp, $cls]) {
            DB::table('incidents')->where('severity', $sev)->update([
                'anomaly_max_hp' => $hp,
                'required_responder_class' => $cls,
            ]);
            // Also align anomaly_hp if currently null
            DB::table('incidents')->where('severity', $sev)->whereNull('anomaly_hp')->update([
                'anomaly_hp' => $hp,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'required_responder_class')) {
                $table->dropColumn('required_responder_class');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_severity_check');
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_severity_check CHECK (severity::text = ANY (ARRAY['LOW'::character varying, 'MEDIUM'::character varying, 'HIGH'::character varying, 'CRITICAL'::character varying]::text[]))");
        }
    }
};
