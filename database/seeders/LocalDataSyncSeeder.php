<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\ResponderAssignment;
use App\Models\User;
use App\Models\WardStation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocalDataSyncSeeder extends Seeder
{
    /**
     * Seed local database snapshot into the target environment (e.g. Railway).
     */
    public function run(): void
    {
        $filePath = database_path('data/local_sync.json');
        if (!File::exists($filePath)) {
            return;
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);
        if (!$data) {
            return;
        }

        // 1. Sync Users
        if (!empty($data['users'])) {
            foreach ($data['users'] as $u) {
                User::updateOrCreate(
                    ['email' => $u['email']],
                    $u
                );
            }
        }

        // 2. Sync Ward Stations & Safe Zones
        if (!empty($data['ward_stations'])) {
            foreach ($data['ward_stations'] as $ws) {
                WardStation::updateOrCreate(
                    ['code' => $ws['code']],
                    $ws
                );
            }
        }

        // 3. Sync Incidents
        if (!empty($data['incidents'])) {
            foreach ($data['incidents'] as $inc) {
                Incident::updateOrCreate(
                    ['id' => $inc['id']],
                    $inc
                );
            }
        }

        // 4. Sync Incident Evidence
        if (!empty($data['incident_evidences'])) {
            foreach ($data['incident_evidences'] as $ev) {
                IncidentEvidence::updateOrCreate(
                    ['id' => $ev['id']],
                    $ev
                );
            }
        }

        // 5. Sync Investigations History
        if (!empty($data['investigations'])) {
            foreach ($data['investigations'] as $inv) {
                Investigation::updateOrCreate(
                    ['id' => $inv['id']],
                    $inv
                );
            }
        }

        // 6. Sync Responder Assignments
        if (!empty($data['responder_assignments'])) {
            foreach ($data['responder_assignments'] as $ra) {
                ResponderAssignment::updateOrCreate(
                    ['incident_id' => $ra['incident_id']],
                    $ra
                );
            }
        }

        // Reset Postgres auto-increment sequences so future inserts don't collide
        if (DB::getDriverName() === 'pgsql') {
            $tables = ['users', 'ward_stations', 'incidents', 'incident_evidence', 'investigations', 'responder_assignments'];
            foreach ($tables as $table) {
                try {
                    $maxId = DB::table($table)->max('id');
                    if ($maxId) {
                        DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$maxId})");
                    }
                } catch (\Throwable $e) {
                    // Ignore if sequence not found
                }
            }
        }
    }
}
