<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\User;
use App\Services\SafeZoneService;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafeZoneProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
    }

    private function getReporter(): User
    {
        return User::where('role', 'reporter')->first();
    }

    public function test_incident_inside_safe_zone_is_rejected_with_exact_message(): void
    {
        $reporter = $this->getReporter();
        $barangay = Barangay::where('name', 'Hubang')->first();

        // Hubang Safe Haven center: 8.5310, 125.9730 (radius 550m)
        $response = $this->actingAs($reporter)->postJson('/incidents', [
            'incident_type' => 'Spectral Activity',
            'title'         => 'Test Anomaly Inside Safe Zone',
            'description'   => 'Trying to report inside Hubang Sanctuary.',
            'latitude'      => 8.5310,
            'longitude'     => 125.9730,
            'barangay_id'   => $barangay->id,
            'severity'      => 'HIGH',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'title'   => SafeZoneService::BLOCKED_TITLE,
            'message' => SafeZoneService::BLOCKED_MESSAGE,
        ]);
    }

    public function test_api_submission_inside_safe_zone_is_blocked(): void
    {
        $reporter = $this->getReporter();

        // Asscat/Gymnasium Sanctuary: 8.5098, 125.9780 (radius 450m)
        $response = $this->actingAs($reporter)->postJson('/api/spectral/incidents', [
            'incident_type' => 'Ectoplasmic Anomaly',
            'title'         => 'Test API Inside Gymnasium Safe Zone',
            'description'   => 'Testing API restriction within protected radius.',
            'latitude'      => 8.5098,
            'longitude'     => 125.9780,
            'severity'      => 'MEDIUM',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'title'   => SafeZoneService::BLOCKED_TITLE,
            'message' => SafeZoneService::BLOCKED_MESSAGE,
        ]);
    }

    public function test_incident_outside_all_safe_zones_submits_successfully(): void
    {
        $reporter = $this->getReporter();
        $barangay = Barangay::where('name', 'Hubang')->first();

        // 8.5370, 125.9730 is ~667m away from Hubang center (outside 550m radius)
        $response = $this->actingAs($reporter)->postJson('/incidents', [
            'incident_type' => 'Spectral Activity',
            'title'         => 'Valid Incident Outside Safe Zone',
            'description'   => 'Reporting outside the protected radius.',
            'latitude'      => 8.5370,
            'longitude'     => 125.9730,
            'barangay_id'   => $barangay->id,
            'severity'      => 'LOW',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('incidents', [
            'title' => 'Valid Incident Outside Safe Zone',
        ]);
    }
}
