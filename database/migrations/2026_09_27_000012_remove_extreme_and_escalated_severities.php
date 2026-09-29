<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update any existing incidents with EXTREME or ESCALATED severity to CRITICAL
        DB::table('incidents')
            ->whereIn('severity', ['EXTREME', 'ESCALATED'])
            ->update([
                'severity'                 => 'CRITICAL',
                'anomaly_max_hp'           => 150,
                'anomaly_hp'               => 150,
                'required_responder_class' => 'A',
            ]);

        // 2. Update any incidents with ESCALATED status to VERIFIED
        DB::table('incidents')
            ->where('status', 'ESCALATED')
            ->update([
                'status' => 'VERIFIED',
            ]);

        // 3. PostgreSQL constraint update: only allow LOW, MEDIUM, HIGH, CRITICAL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_severity_check');
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_severity_check CHECK (severity::text = ANY (ARRAY['LOW'::character varying, 'MEDIUM'::character varying, 'HIGH'::character varying, 'CRITICAL'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_severity_check');
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_severity_check CHECK (severity::text = ANY (ARRAY['LOW'::character varying, 'MEDIUM'::character varying, 'HIGH'::character varying, 'EXTREME'::character varying, 'ESCALATED'::character varying, 'CRITICAL'::character varying]::text[]))");
        }
    }
};
