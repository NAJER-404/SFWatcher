<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NominatimAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
    }

    public function test_reverse_geocode_api_with_mocked_nominatim(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Roxas Street, Barangay 3, San Francisco, Agusan del Sur, Philippines',
                'address' => [
                    'road'    => 'Roxas Street',
                    'quarter' => 'Barangay 3',
                    'town'    => 'San Francisco',
                    'state'   => 'Agusan del Sur',
                    'country' => 'Philippines',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/spectral/reverse-geocode?lat=8.5070&lng=125.9775');

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'barangay'     => 'Barangay 3',
            'municipality' => 'San Francisco',
            'province'     => 'Agusan del Sur',
            'country'      => 'Philippines',
        ]);
        $this->assertStringContainsString('Barangay 3', $response->json('address'));
    }

    public function test_reverse_geocode_api_fallback_when_external_service_fails(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/spectral/reverse-geocode?lat=8.5310&lng=125.9730');

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'municipality' => 'San Francisco',
            'province'     => 'Agusan del Sur',
            'country'      => 'Philippines',
        ]);
        $this->assertNotEmpty($response->json('address'));
    }

    public function test_store_incident_without_manual_date_uses_current_timestamp(): void
    {
        $reporter = User::where('role', 'reporter')->first();

        $payload = [
            'incident_type' => 'Spirit Activity',
            'title'         => 'Anomalous resonance near Hubang',
            'description'   => 'Detected spectral readings during patrol.',
            'latitude'      => 8.5310,
            'longitude'     => 125.9730,
            'severity'      => 'HIGH',
        ];

        $response = $this->actingAs($reporter)->postJson('/api/spectral/incidents', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'incident_type' => 'Spirit Activity',
                'title'         => 'Anomalous resonance near Hubang',
                'severity'      => 'HIGH',
            ],
        ]);

        $this->assertDatabaseHas('incidents', [
            'title'    => 'Anomalous resonance near Hubang',
            'severity' => 'HIGH',
        ]);
    }

    public function test_dashboard_does_not_contain_street_view_and_has_nominatim_field(): void
    {
        $user = User::where('email', 'reporter@ectonet.gov')->first();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);

        // Street View elements must be gone
        $response->assertDontSee('streetview-toggle-btn');
        $response->assertDontSee('streetview-left-panel');
        $response->assertDontSee('streetview-canvas');

        // Redundant floating HUD must be gone
        $response->assertDontSee('map-hud-incidents');

        // Nominatim resolved location field must be removed
        $response->assertDontSee('id="report-resolved-location"', false);
        $response->assertDontSee('Readable Location (Nominatim)');

        // Manual date & time input must NOT be present in modal
        $response->assertDontSee('id="report-datetime"', false);
    }
}
