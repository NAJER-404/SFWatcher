<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\ResponderAssignment;
use App\Models\WardStation;
use App\Models\Resource;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;

echo "=== SPECTRAWATCH POSTGRESQL 18 COMPREHENSIVE VERIFICATION ===\n\n";

// 1. Connection test
$dbName = DB::connection()->getDatabaseName();
$driver = DB::connection()->getDriverName();
echo "1. Database Driver: $driver | Database Name: $dbName\n";
if ($driver !== 'pgsql') {
    throw new Exception("Driver is not pgsql!");
}

// 2. Models & Row count checks
echo "\n2. Model Counts:\n";
$models = [
    'User' => User::count(),
    'Barangay' => Barangay::count(),
    'Incident' => Incident::count(),
    'IncidentEvidence' => IncidentEvidence::count(),
    'Investigation' => Investigation::count(),
    'ResponderAssignment' => ResponderAssignment::count(),
    'WardStation' => WardStation::count(),
    'Resource' => Resource::count(),
    'Equipment' => Equipment::count(),
];

foreach ($models as $name => $count) {
    echo "  - $name: $count rows\n";
}

// 3. Eloquent Relationships test
echo "\n3. Testing Eloquent Relationships:\n";
$incident = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responderAssignments.responder'])->first();
if ($incident) {
    echo "  - Incident: {$incident->incident_code} - {$incident->title}\n";
    echo "    - Barangay: " . ($incident->barangay ? $incident->barangay->name : 'None') . "\n";
    echo "    - Reporter: " . ($incident->reporter ? $incident->reporter->name : 'None') . "\n";
    echo "    - Evidence count: " . $incident->evidence->count() . "\n";
    echo "    - Investigations count: " . $incident->investigations->count() . "\n";
    echo "    - Assignments count: " . $incident->responderAssignments->count() . "\n";
    echo "    [✓] All relationships loaded successfully!\n";
} else {
    echo "  [!] No incidents found.\n";
}

// 4. Test CRUD Operation with Sequence Auto-Increment
echo "\n4. Testing CRUD Operation & Sequence Auto-Increment:\n";
$barangay = Barangay::first();
$user = User::first();

// CREATE
$testIncident = Incident::create([
    'incident_code' => 'TEST-INC-' . time(),
    'reported_by' => $user->id,
    'barangay_id' => $barangay->id,
    'incident_type' => 'Spectral Residue',
    'title' => 'PostgreSQL Verification Incident',
    'description' => 'Testing CRUD operations on local PostgreSQL 18',
    'latitude' => 8.531234,
    'longitude' => 125.971234,
    'incident_date' => now(),
    'severity' => 'LOW',
    'status' => 'PENDING',
    'notes' => 'Created during migration verification'
]);
echo "  [✓] CREATE: Created incident with ID {$testIncident->id} and Code {$testIncident->incident_code}\n";

// READ
$readIncident = Incident::find($testIncident->id);
if (!$readIncident || $readIncident->title !== 'PostgreSQL Verification Incident') {
    throw new Exception("READ failed!");
}
echo "  [✓] READ: Successfully read incident ID {$readIncident->id}\n";

// UPDATE
$readIncident->update([
    'title' => 'PostgreSQL Verification Incident (Updated)',
    'severity' => 'HIGH'
]);
$updatedIncident = Incident::find($testIncident->id);
if ($updatedIncident->title !== 'PostgreSQL Verification Incident (Updated)' || $updatedIncident->severity !== 'HIGH') {
    throw new Exception("UPDATE failed!");
}
echo "  [✓] UPDATE: Successfully updated incident title and severity\n";

// DELETE
$readIncident->delete();
$deletedIncident = Incident::find($testIncident->id);
if ($deletedIncident !== null) {
    throw new Exception("DELETE failed!");
}
echo "  [✓] DELETE: Successfully deleted test incident\n";

echo "\n[✓] ALL TESTS PASSED SUCCESSFULLY ON POSTGRESQL 18!\n";
