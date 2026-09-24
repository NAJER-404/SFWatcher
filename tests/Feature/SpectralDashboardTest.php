<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpectralDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);
        $this->user = User::where('role', 'investigator')->first();
    }

    public function test_spectral_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/spectral');

        $response->assertStatus(200);
        $response->assertSee('Spectra');
        $response->assertSee('Incident');
        $response->assertSee('San Francisco, Agusan del Sur');
        $response->assertSee('id="spectral-map"', false);
        $response->assertSee('id="location-picker-hud"', false);
        $response->assertSee('id="report-modal"', false);
        $response->assertSee('css/spectral.css');
        $response->assertSee('js/spectral.js');
    }

    public function test_ecto_alias_redirects_to_spectral(): void
    {
        // Authenticated: /ecto should redirect to spectral.dashboard
        $response = $this->actingAs($this->user)->get('/ecto');
        $response->assertRedirect(route('spectral.dashboard'));
    }

    public function test_unauthenticated_access_redirects_to_login(): void
    {
        $this->get('/spectral')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
    }

    public function test_my_reports_displays_only_authenticated_user_reports(): void
    {
        $reporterMorales = User::where('email', 'morales@ectonet.gov')->first();
        $reporterAlcantara = User::where('email', 'reporter@ectonet.gov')->first();

        // Morales reports (SF-INC-001, SF-INC-003, SF-INC-006)
        $responseMorales = $this->actingAs($reporterMorales)->get('/my-reports');
        $responseMorales->assertStatus(200);
        $responseMorales->assertSee('SF-INC-001');
        $responseMorales->assertSee('SF-INC-003');
        $responseMorales->assertDontSee('SF-INC-002'); // Belonging to Alcantara

        // Alcantara reports (SF-INC-002, SF-INC-005, SF-INC-007)
        $responseAlcantara = $this->actingAs($reporterAlcantara)->get('/my-reports');
        $responseAlcantara->assertStatus(200);
        $responseAlcantara->assertSee('SF-INC-002');
        $responseAlcantara->assertDontSee('SF-INC-001'); // Belonging to Morales
    }

    public function test_my_reports_sidebar_shows_active_incidents_count(): void
    {
        $reporter = User::where('email', 'morales@ectonet.gov')->first();
        $response = $this->actingAs($reporter)->get('/my-reports');
        $response->assertStatus(200);
        // Sidebar incidents count must match active incidents, not 0
        $this->assertGreaterThan(0, $response->viewData('stats')['active_incidents']);
    }

    public function test_notifications_can_be_marked_as_read(): void
    {
        $reporter = User::where('email', 'morales@ectonet.gov')->first();

        // Mark individual notification as read
        $res1 = $this->actingAs($reporter)->postJson('/notifications/mark-as-read', [
            'incident_id' => 1,
        ]);
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        // Mark all as read
        $resAll = $this->actingAs($reporter)->postJson('/notifications/mark-as-read', [
            'all' => true,
        ]);
        $resAll->assertStatus(200);
        $resAll->assertJson(['success' => true]);
    }

    public function test_my_reports_is_sorted_with_pending_first(): void
    {
        $reporter = User::where('email', 'morales@ectonet.gov')->first();

        // Create a pending incident and a resolved incident for Morales
        \App\Models\Incident::create([
            'reported_by'   => $reporter->id,
            'incident_type' => 'Spirit Activity',
            'title'         => 'Resolved Anomaly',
            'description'   => 'Resolved item',
            'latitude'      => 8.50,
            'longitude'     => 125.90,
            'severity'      => 'LOW',
            'status'        => 'RESOLVED',
            'incident_date' => now(),
        ]);

        \App\Models\Incident::create([
            'reported_by'   => $reporter->id,
            'incident_type' => 'Spirit Activity',
            'title'         => 'Pending Anomaly',
            'description'   => 'Pending item',
            'latitude'      => 8.50,
            'longitude'     => 125.90,
            'severity'      => 'HIGH',
            'status'        => 'PENDING',
            'incident_date' => now(),
        ]);

        $response = $this->actingAs($reporter)->get('/my-reports');
        $response->assertStatus(200);

        $incidents = $response->viewData('incidents');
        $this->assertEquals('PENDING', $incidents->first()->status);
    }

    public function test_reporter_can_delete_own_incident(): void
    {
        $reporter = User::where('email', 'morales@ectonet.gov')->first();

        $incident = \App\Models\Incident::create([
            'reported_by'   => $reporter->id,
            'incident_type' => 'Poltergeist Disturbance',
            'title'         => 'Incident To Delete',
            'description'   => 'To be deleted',
            'latitude'      => 8.51,
            'longitude'     => 125.95,
            'severity'      => 'LOW',
            'status'        => 'PENDING',
            'incident_date' => now(),
        ]);

        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);

        $deleteResponse = $this->actingAs($reporter)->delete("/incidents/{$incident->id}");
        $deleteResponse->assertRedirect('/my-reports');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_reporter_cannot_delete_other_user_incident(): void
    {
        $morales   = User::where('email', 'morales@ectonet.gov')->first();
        $alcantara = User::where('email', 'reporter@ectonet.gov')->first();

        $incident = \App\Models\Incident::create([
            'reported_by'   => $morales->id,
            'incident_type' => 'Poltergeist Disturbance',
            'title'         => 'Morales Incident',
            'description'   => 'Protected',
            'latitude'      => 8.51,
            'longitude'     => 125.95,
            'severity'      => 'LOW',
            'status'        => 'PENDING',
            'incident_date' => now(),
        ]);

        // Alcantara attempts to delete Morales' incident
        $deleteResponse = $this->actingAs($alcantara)->delete("/incidents/{$incident->id}");
        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }
}

