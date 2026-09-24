<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Investigation;
use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestigatorRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $investigator;
    private User $reporter;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);

        $this->investigator = User::where('role', 'investigator')->first();
        $this->reporter     = User::where('role', 'reporter')->first();
        $this->admin        = User::where('role', 'admin')->first();
    }

    public function test_investigator_login_page_renders_with_investigator_portal_branding(): void
    {
        $response = $this->get('/investigator/login');

        $response->assertStatus(200);
        $response->assertSee('INVESTIGATOR LOGIN');
        $response->assertSee('SpectraWatch');
        $response->assertSee('Investigator Portal');
        $response->assertSee('Sign in as Reporter');
        $response->assertDontSee('Continue with Google');
    }

    public function test_investigator_can_login_and_is_redirected_to_investigator_dashboard(): void
    {
        $response = $this->post('/investigator/login', [
            'email'    => $this->investigator->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('investigator.dashboard'));
        $this->assertAuthenticatedAs($this->investigator, 'investigator');
    }

    public function test_reporter_cannot_login_through_investigator_portal(): void
    {
        $response = $this->post('/investigator/login', [
            'email'    => $this->reporter->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'You are not authorized to access the Investigator Portal.',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_admin_cannot_login_through_investigator_portal(): void
    {
        $response = $this->post('/investigator/login', [
            'email'    => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'You are not authorized to access the Investigator Portal.',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_reporter_cannot_access_investigator_dashboard_via_middleware(): void
    {
        // A reporter logged in via web guard should NOT be able to access
        // the investigator dashboard — middleware now redirects to investigator login
        // (because the investigator guard session is empty, not the web guard user).
        $response = $this->actingAs($this->reporter, 'web')->get('/investigator/dashboard');

        // Middleware redirects to investigator login (no investigator guard session)
        $response->assertRedirect(route('investigator.login'));
    }

    public function test_guest_cannot_access_investigator_dashboard(): void
    {
        $response = $this->get('/investigator/dashboard');

        $response->assertRedirect(route('investigator.login'));
    }

    public function test_investigator_dashboard_renders_with_real_database_statistics(): void
    {
        // Must use 'investigator' guard to pass EnsureInvestigator middleware
        $response = $this->actingAs($this->investigator, 'investigator')->get('/investigator/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Active Incidents');
        $response->assertSee('Pending Review');
        $response->assertSee('Under Investigation');
        $response->assertSee('High Severity');
        $response->assertSee('Resolved');
        $response->assertSee('Active Investigation Queue');
        $response->assertSee('id="investigator-dashboard-map"', false);
    }

    public function test_investigation_queue_page_renders_correctly(): void
    {
        $response = $this->actingAs($this->investigator, 'investigator')->get('/investigator/queue');

        $response->assertStatus(200);
        $response->assertSee('Investigation Queue');
        $response->assertSee('Filter Queue');
    }

    public function test_incident_review_page_renders_with_details_and_evidence_handling(): void
    {
        $incident = Incident::first();
        $this->assertNotNull($incident);

        $response = $this->actingAs($this->investigator, 'investigator')->get("/investigator/incidents/{$incident->id}/review");

        $response->assertStatus(200);
        $response->assertSee($incident->incident_code);
        $response->assertSee($incident->title);
        $response->assertSee('Investigator Actions');
        $response->assertSee('Audit Trail');
        $response->assertSee('id="investigator-review-mini-map"', false);

    }

    public function test_investigator_can_update_status_severity_and_record_notes(): void
    {
        $incident = Incident::where('status', 'PENDING')->first() ?? Incident::first();
        $this->assertNotNull($incident);

        $note = 'Evidence reviewed and location confirmed. Additional response recommended.';

        $response = $this->actingAs($this->investigator, 'investigator')->put("/investigator/incidents/{$incident->id}", [
            'status'   => 'VERIFIED',
            'severity' => 'HIGH',
            'notes'    => $note,
        ]);

        $response->assertRedirect();
        $incident->refresh();

        $this->assertEquals('VERIFIED', $incident->status);
        $this->assertEquals('HIGH', $incident->severity);

        $this->assertDatabaseHas('investigations', [
            'incident_id'     => $incident->id,
            'investigator_id' => $this->investigator->id,
            'result'          => 'VERIFIED',
            'notes'           => $note,
        ]);
    }

    public function test_reporter_cannot_modify_incident_status_via_put_request(): void
    {
        $incident = Incident::first();

        // Testing standard incident status update endpoint
        $response = $this->actingAs($this->reporter)->put("/incidents/{$incident->id}/status", [
            'status'   => 'RESOLVED',
            'severity' => 'LOW',
            'notes'    => 'Malicious attempt by reporter',
        ]);

        $response->assertStatus(403);
    }

    public function test_investigator_and_reporter_can_be_logged_in_simultaneously_without_collision(): void
    {
        // 1. Reporter logs in on web guard
        $this->post('/login', [
            'email'    => $this->reporter->email,
            'password' => 'password',
        ])->assertRedirect(route('spectral.dashboard'));

        $this->assertAuthenticatedAs($this->reporter, 'web');

        // 2. Investigator logs in on investigator guard
        $this->post('/investigator/login', [
            'email'    => $this->investigator->email,
            'password' => 'password',
        ])->assertRedirect(route('investigator.dashboard'));

        $this->assertAuthenticatedAs($this->investigator, 'investigator');

        // Both guards are active simultaneously and do not collide
        $this->assertTrue(\Illuminate\Support\Facades\Auth::guard('web')->check());
        $this->assertTrue(\Illuminate\Support\Facades\Auth::guard('investigator')->check());
    }

    public function test_investigator_logout_does_not_log_out_reporter(): void
    {
        // Log into both guards
        $this->post('/login', [
            'email'    => $this->reporter->email,
            'password' => 'password',
        ]);
        $this->post('/investigator/login', [
            'email'    => $this->investigator->email,
            'password' => 'password',
        ]);

        // Log out of investigator portal
        $this->post('/investigator/logout')
            ->assertRedirect(route('investigator.login'));

        // Investigator is logged out, but reporter is still authenticated!
        $this->assertFalse(\Illuminate\Support\Facades\Auth::guard('investigator')->check());
        $this->assertTrue(\Illuminate\Support\Facades\Auth::guard('web')->check());
    }

    public function test_investigator_can_view_responders_roster_with_health_and_classes_without_xp(): void
    {
        $response = $this->actingAs($this->investigator, 'investigator')->get('/investigator/responders');

        $response->assertStatus(200);
        $response->assertSee('Tactical Field Responders');
        $response->assertSee('Health (HP)');
        $response->assertSee('Class');
        $response->assertDontSee('XP');
        $response->assertDontSee('EXP');
    }
}
