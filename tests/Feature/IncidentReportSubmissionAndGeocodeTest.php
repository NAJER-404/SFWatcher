<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IncidentReportSubmissionAndGeocodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
    }

    /**
     * Test that clicking on Barangay 3 coordinates resolves to Barangay 3,
     * even if Nominatim erroneously returns "Karaos" village.
     */
    public function test_barangay_3_coordinates_resolve_accurately_not_karaos(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Quezon Street, Karaos, San Francisco, Agusan del Sur, Philippines',
                'address' => [
                    'road'    => 'Quezon Street',
                    'village' => 'Karaos',
                    'town'    => 'San Francisco',
                    'state'   => 'Agusan del Sur',
                    'country' => 'Philippines',
                ],
            ], 200),
        ]);

        // Coordinates at Barangay 3 Poblacion: 8.5078, 125.9735
        $response = $this->getJson('/api/spectral/reverse-geocode?lat=8.5110&lng=125.9720');

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'barangay' => 'Barangay 3',
        ]);
        $this->assertEquals('Barangay 3', $response->json('barangay'));
    }

    /**
     * Test that clicking on Barangay 5 coordinates resolves to Barangay 5,
     * even if Nominatim erroneously returns "Hubang" village.
     */
    public function test_barangay_5_coordinates_resolve_accurately_not_hubang(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Rizal Avenue, Hubang, San Francisco, Agusan del Sur, Philippines',
                'address' => [
                    'road'    => 'Rizal Avenue',
                    'village' => 'Hubang',
                    'town'    => 'San Francisco',
                    'state'   => 'Agusan del Sur',
                    'country' => 'Philippines',
                ],
            ], 200),
        ]);

        // Coordinates at Barangay 5 Poblacion: 8.5041, 125.9790
        $response = $this->getJson('/api/spectral/reverse-geocode?lat=8.5029&lng=125.9781');

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'barangay' => 'Barangay 5',
        ]);
        $this->assertEquals('Barangay 5', $response->json('barangay'));
    }

    /**
     * Test incident submission via web POST /incidents with JSON accept header.
     */
    public function test_user_can_submit_incident_via_web_route(): void
    {
        $reporter = User::where('role', 'reporter')->first();
        $barangay3 = Barangay::where('name', 'Barangay 3')->first();

        $payload = [
            'incident_type' => 'Ectoplasmic Anomaly',
            'title'         => 'Ghost sighting at central terminal',
            'description'   => 'Luminescent anomaly manifested near the public market.',
            'barangay_id'   => $barangay3->id,
            'latitude'      => 8.5110,
            'longitude'     => 125.9720,
            'severity'      => 'HIGH',
        ];

        $response = $this->actingAs($reporter)->post('/incidents', $payload, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('incidents', [
            'title'       => 'Ghost sighting at central terminal',
            'barangay_id' => $barangay3->id,
            'severity'    => 'HIGH',
            'status'      => 'PENDING',
        ]);
    }

    /**
     * Test incident submission when barangay_id is omitted:
     * coordinates auto-resolve to the correct San Francisco barangay.
     */
    public function test_incident_submission_auto_resolves_barangay_from_coordinates(): void
    {
        $reporter = User::where('role', 'reporter')->first();
        $barangay5 = Barangay::where('name', 'Barangay 5')->first();

        $payload = [
            'incident_type' => 'Spirit Activity',
            'title'         => 'Spectral cold spot near Brgy 5 gym',
            'description'   => 'Temperature drop recorded.',
            'latitude'      => 8.5029,
            'longitude'     => 125.9781,
            'severity'      => 'MEDIUM',
        ];

        $response = $this->actingAs($reporter)->post('/incidents', $payload, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('incidents', [
            'title'       => 'Spectral cold spot near Brgy 5 gym',
            'barangay_id' => $barangay5->id,
            'severity'    => 'MEDIUM',
        ]);
    }

    public function test_hubang_resolves_accurately_with_nominatim_hint(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'National Highway, Hubang, San Francisco, Agusan del Sur, Philippines',
                'address' => [
                    'road'    => 'National Highway',
                    'village' => 'Hubang',
                    'town'    => 'San Francisco',
                    'state'   => 'Agusan del Sur',
                    'country' => 'Philippines',
                ],
            ], 200),
        ]);

        $hubangRes = $this->getJson('/api/spectral/reverse-geocode?lat=8.5285&lng=126.0150');
        $hubangRes->assertStatus(200);
        $this->assertEquals('Hubang', $hubangRes->json('barangay'));
    }

    public function test_pisaan_resolves_accurately_with_nominatim_hint(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Pisa-an Access Road, Pisa-an, San Francisco, Agusan del Sur, Philippines',
                'address' => [
                    'road'    => 'Pisa-an Access Road',
                    'village' => 'Pisa-an',
                    'town'    => 'San Francisco',
                    'state'   => 'Agusan del Sur',
                    'country' => 'Philippines',
                ],
            ], 200),
        ]);

        $pisaanRes = $this->getJson('/api/spectral/reverse-geocode?lat=8.4230&lng=125.9645');
        $pisaanRes->assertStatus(200);
        $this->assertEquals('Pisa-an', $pisaanRes->json('barangay'));
    }

    public function test_incident_code_generation_never_collides_with_existing_codes(): void
    {
        $reporter = User::where('role', 'reporter')->first();

        // Simulate existing SF-INC-013 and SF-INC-014 in database
        \App\Models\Incident::create([
            'incident_code' => 'SF-INC-013',
            'reported_by'   => $reporter->id,
            'incident_type' => 'Spirit Activity',
            'title'         => 'Test Collision 13',
            'description'   => 'Test',
            'latitude'      => 8.5132,
            'longitude'     => 125.9765,
            'severity'      => 'LOW',
            'status'        => 'PENDING',
        ]);

        \App\Models\Incident::create([
            'incident_code' => 'SF-INC-014',
            'reported_by'   => $reporter->id,
            'incident_type' => 'Spirit Activity',
            'title'         => 'Test Collision 14',
            'description'   => 'Test',
            'latitude'      => 8.5132,
            'longitude'     => 125.9765,
            'severity'      => 'LOW',
            'status'        => 'PENDING',
        ]);

        // Creating without incident_code must automatically generate a new unique code (SF-INC-015+)
        $newIncident = \App\Models\Incident::create([
            'reported_by'   => $reporter->id,
            'incident_type' => 'Spirit Activity',
            'title'         => 'New Non-colliding Incident',
            'description'   => 'Test',
            'latitude'      => 8.5132,
            'longitude'     => 125.9765,
            'severity'      => 'LOW',
            'status'        => 'PENDING',
        ]);

        $this->assertNotEquals('SF-INC-013', $newIncident->incident_code);
        $this->assertNotEquals('SF-INC-014', $newIncident->incident_code);
        $this->assertStringStartsWith('SF-INC-', $newIncident->incident_code);
    }
}