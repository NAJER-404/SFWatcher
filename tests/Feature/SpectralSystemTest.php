<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\Resource;
use App\Models\User;
use App\Models\WardStation;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpectralSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $investigator;
    private User $reporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
        $this->investigator = User::where('role', 'investigator')->first();
        $this->reporter     = User::where('role', 'reporter')->first();
    }

    public function test_dashboard_renders_with_real_database_records(): void
    {
        $response = $this->actingAs($this->investigator)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Spectra');
        $response->assertSee('San Francisco, Agusan del Sur');
        $response->assertSee('SF-INC-001');
        $response->assertSee('id="spectral-map"', false);
        $response->assertSee('id="location-picker-hud"', false);
    }

    public function test_incident_registry_and_details_page(): void
    {
        $incident = Incident::first();
        $this->assertNotNull($incident);

        // Registry list
        $listResponse = $this->actingAs($this->reporter)->get('/incidents');
        $listResponse->assertStatus(200);
        $listResponse->assertSee($incident->incident_code);

        // Details show
        $showResponse = $this->actingAs($this->reporter)->get("/incidents/{$incident->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($incident->incident_code);
        $showResponse->assertSee($incident->title);
        $showResponse->assertSee('id="mini-incident-map"', false);
    }

    public function test_report_incident_web_and_file_upload(): void
    {
        Storage::fake('public');

        $barangay = Barangay::where('name', 'Hubang')->first();

        $response = $this->actingAs($this->reporter)->post('/incidents', [
            'incident_type' => 'Ectoplasmic Anomaly',
            'title'         => 'High-Viscosity Surge near Hubang Market',
            'description'   => 'Luminescent puddle detected emitting 428 THz resonance.',
            'barangay_id'   => $barangay->id,
            'latitude'      => 8.5310,
            'longitude'     => 125.9730,
            'incident_date' => now()->toDateTimeString(),
            'severity'      => 'HIGH',
            'evidence'      => UploadedFile::fake()->create('ectoplasm_sample.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('incidents', [
            'title'    => 'High-Viscosity Surge near Hubang Market',
            'severity' => 'HIGH',
            'status'   => 'PENDING',
        ]);

        $incident = Incident::where('title', 'High-Viscosity Surge near Hubang Market')->first();
        $this->assertNotNull($incident);
        $this->assertCount(1, $incident->evidence);
        Storage::disk('public')->assertExists($incident->evidence->first()->file_path);
    }

    public function test_investigator_workflow_advances_status_and_records_investigation(): void
    {
        $incident = Incident::where('status', 'PENDING')->first();
        $this->assertNotNull($incident);

        $response = $this->actingAs($this->investigator)->put("/incidents/{$incident->id}/status", [
            'status'   => 'UNDER INVESTIGATION',
            'severity' => 'CRITICAL',
            'notes'    => 'Dispatched Warden strike team with containment vessel #03.',
        ]);

        $response->assertRedirect();
        $incident->refresh();

        $this->assertEquals('UNDER INVESTIGATION', $incident->status);
        $this->assertEquals('CRITICAL', $incident->severity);

        $this->assertDatabaseHas('investigations', [
            'incident_id' => $incident->id,
            'result'      => 'UNDER INVESTIGATION',
            'notes'       => 'Dispatched Warden strike team with containment vessel #03.',
        ]);
    }

    public function test_spectral_rest_api_endpoints(): void
    {
        // 1. Incidents API
        $incResponse = $this->getJson('/api/spectral/incidents');
        $incResponse->assertStatus(200);
        $incResponse->assertJsonStructure(['success', 'count', 'incidents']);
        $this->assertGreaterThanOrEqual(7, $incResponse->json('count'));

        // 2. Ward Stations API
        $wardResponse = $this->getJson('/api/spectral/wards');
        $wardResponse->assertStatus(200);
        $wardResponse->assertJsonStructure(['success', 'count', 'wards']);
        $this->assertGreaterThanOrEqual(5, $wardResponse->json('count'));

        // 3. Resources API
        $resResponse = $this->getJson('/api/spectral/resources');
        $resResponse->assertStatus(200);
        $resResponse->assertJsonStructure(['success', 'count', 'resources']);
        $this->assertGreaterThanOrEqual(4, $resResponse->json('count'));

        // 4. Barangays API (27 Barangays)
        $brgyResponse = $this->getJson('/api/spectral/barangays');
        $brgyResponse->assertStatus(200);
        $this->assertEquals(27, $brgyResponse->json('count'));

        // 5. Stats API
        $statsResponse = $this->getJson('/api/spectral/stats');
        $statsResponse->assertStatus(200);
        $statsResponse->assertJson(['success' => true]);
    }

    public function test_ward_resources_and_equipment_pages(): void
    {
        $this->actingAs($this->investigator)->get('/wards')->assertStatus(200)->assertSee('Hubang Master Spirit Ward Station');
        $this->actingAs($this->investigator)->get('/resources')->assertStatus(200)->assertSee('Hubang Ectoplasm Well #1');
        $this->actingAs($this->investigator)->get('/equipment')->assertStatus(200)->assertSee('Aegis-IV Spirit Ward Generator');
    }
}

