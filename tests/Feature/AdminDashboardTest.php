<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\PromotionHistory;
use App\Models\ResponderAssignment;
use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $investigator;
    private User $responder;
    private User $reporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpectralSeeder::class);

        $this->admin        = User::where('role', 'admin')->first();
        $this->investigator = User::where('role', 'investigator')->first();
        $this->responder    = User::where('role', 'responder')->first();
        $this->reporter     = User::where('role', 'reporter')->first();
    }

    public function test_admin_login_page_renders(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Administrator');
        $response->assertSee(route('admin.login.submit'));
    }

    public function test_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email'    => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_non_admin_cannot_login_through_admin_portal(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email'    => $this->reporter->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_reporter_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->reporter, 'web')->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_investigator_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->investigator, 'investigator')->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_dashboard_renders_with_real_database_statistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->admin->name);
        $response->assertSee('USER STATISTICS');
        $response->assertSee('INCIDENT STATISTICS');
        $response->assertSee('RESPONDER STATISTICS');
        $response->assertSee('RECENT SYSTEM ACTIVITY');
        // Verify no GIS map is rendered
        $response->assertDontSee('id="spectral-map"', false);
        $response->assertDontSee('id="investigator-dashboard-map"', false);
    }

    public function test_admin_can_view_all_users_with_search_and_filters(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.users.index', [
            'search' => $this->responder->name,
        ]));

        $response->assertStatus(200);
        $response->assertSee($this->responder->name);
        $response->assertSee('Class ' . $this->responder->responder_class);
    }

    public function test_admin_can_inspect_user_profile(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.users.show', $this->responder));

        $response->assertStatus(200);
        $response->assertSee($this->responder->name);
        $response->assertSee($this->responder->email);
        $response->assertSee('Tactical Profile');
        $response->assertSee('Class Progression Trajectory');
    }

    public function test_admin_can_promote_eligible_responder_and_max_hp_updates(): void
    {
        // Set responder as Class D with 120 XP (requires 100 XP for Class C)
        $this->responder->update([
            'responder_class' => 'D',
            'xp'              => 120,
        ]);

        $this->assertTrue($this->responder->isPromotionEligible());

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.users.promote', $this->responder));

        $response->assertSessionHas('success');
        $this->responder->refresh();

        $this->assertEquals('C', $this->responder->responder_class);
        $this->assertEquals(120, $this->responder->max_hp);
        $this->assertEquals(120, $this->responder->xp); // XP preserved

        $this->assertDatabaseHas('promotion_histories', [
            'user_id'    => $this->responder->id,
            'from_class' => 'D',
            'to_class'   => 'C',
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'RESPONDER_PROMOTED',
        ]);
    }

    public function test_ineligible_responder_promotion_is_rejected_without_force(): void
    {
        $this->responder->update([
            'responder_class' => 'D',
            'xp'              => 20, // Requires 100 XP
        ]);

        $this->assertFalse($this->responder->isPromotionEligible());

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.users.promote', $this->responder));

        $response->assertSessionHasErrors('promotion');
        $this->responder->refresh();
        $this->assertEquals('D', $this->responder->responder_class);
    }

    public function test_admin_can_manually_adjust_responder_class(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.users.class', $this->responder), [
            'responder_class' => 'B',
        ]);

        $response->assertSessionHas('success');
        $this->responder->refresh();

        $this->assertEquals('B', $this->responder->responder_class);
        $this->assertEquals(145, $this->responder->max_hp);
    }

    public function test_admin_cannot_demote_themselves_or_primary_admin(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.users.role', $this->admin), [
            'role' => 'reporter',
        ]);

        $response->assertSessionHasErrors('role');
        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role);
    }

    public function test_admin_can_view_all_incidents_across_all_stages(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.incidents.index'));

        $response->assertStatus(200);
        $response->assertSee('All Incidents Registry');
        $response->assertSee('SF-INC-001');
    }

    public function test_admin_can_archive_resolved_incident_from_active_map(): void
    {
        $resolvedIncident = Incident::where('status', 'RESOLVED')->first();
        $this->assertNotNull($resolvedIncident);
        $this->assertNull($resolvedIncident->archived_from_map_at);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.incidents.archive', $resolvedIncident), [
            'archive_notes' => 'Threat completely mitigated. Archiving from field map.',
        ]);

        $response->assertSessionHas('success');
        $resolvedIncident->refresh();

        $this->assertNotNull($resolvedIncident->archived_from_map_at);
        $this->assertEquals($this->admin->id, $resolvedIncident->archived_by);
        $this->assertEquals('RESOLVED', $resolvedIncident->status); // Status preserved!

        $this->assertDatabaseHas('admin_activity_logs', [
            'action'       => 'INCIDENT_ARCHIVED',
            'subject_type' => 'Incident',
            'subject_id'   => $resolvedIncident->id,
        ]);
    }

    public function test_active_incident_cannot_be_archived(): void
    {
        $activeIncident = Incident::where('status', 'UNDER INVESTIGATION')->first();
        $this->assertNotNull($activeIncident);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.incidents.archive', $activeIncident));

        $response->assertSessionHasErrors('archive');
        $activeIncident->refresh();
        $this->assertNull($activeIncident->archived_from_map_at);
    }

    public function test_archived_incident_disappears_from_reporter_and_investigator_active_maps(): void
    {
        $resolvedIncident = Incident::where('status', 'RESOLVED')->first();
        $this->assertNotNull($resolvedIncident);

        // Before archiving: incident is in the activeOnMap scope
        $this->assertTrue(
            Incident::activeOnMap()->where('id', $resolvedIncident->id)->exists(),
            'Resolved incident should be visible on active map before archiving'
        );

        // Admin archives the resolved incident
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.incidents.archive', $resolvedIncident), [
                'archive_notes' => 'Archiving from map test.',
            ]);
        $response->assertSessionHas('success');
        $resolvedIncident->refresh();

        // After archiving: incident is EXCLUDED from activeOnMap scope
        $this->assertFalse(
            Incident::activeOnMap()->where('id', $resolvedIncident->id)->exists(),
            'Archived incident should NOT appear in active map scope'
        );

        // After archiving: incident IS in the archivedFromMap scope
        $this->assertTrue(
            Incident::archivedFromMap()->where('id', $resolvedIncident->id)->exists(),
            'Archived incident should appear in archivedFromMap scope'
        );

        // Status is still RESOLVED (not changed by archiving)
        $this->assertEquals('RESOLVED', $resolvedIncident->status);

        // Still visible in Admin archived incidents page
        $adminHistory = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.incidents.archived'));
        $adminHistory->assertSee($resolvedIncident->incident_code);
    }

    public function test_admin_can_restore_archived_incident_to_active_map(): void
    {
        $resolvedIncident = Incident::where('status', 'RESOLVED')->first();
        $this->assertNotNull($resolvedIncident);

        // Archive first
        $this->actingAs($this->admin, 'admin')->post(route('admin.incidents.archive', $resolvedIncident));
        $resolvedIncident->refresh();
        $this->assertTrue($resolvedIncident->isArchivedFromMap());

        // Restore
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.incidents.restore', $resolvedIncident));
        $response->assertSessionHas('success');

        $resolvedIncident->refresh();
        $this->assertFalse($resolvedIncident->isArchivedFromMap());
        $this->assertEquals('RESOLVED', $resolvedIncident->status); // Status preserved!

        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'INCIDENT_RESTORED',
        ]);
    }

    public function test_admin_can_view_incident_transcript_timeline(): void
    {
        $incident = Incident::first();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.incidents.transcript', $incident));

        $response->assertStatus(200);
        $response->assertSee('CHRONOLOGICAL DEFENSE TIMELINE');
        $response->assertSee($incident->incident_code);
        $response->assertSee('REPORTED');
    }

    public function test_admin_analytics_renders_with_real_database_data(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.analytics'));

        $response->assertStatus(200);
        $response->assertSee('System Statistics & Telemetry');
        $response->assertSee('Incident Resolution Rate');
        $response->assertSee('INCIDENT ANALYTICS');
        $response->assertSee('USER & RESPONDER ANALYTICS');
    }

    public function test_admin_profile_and_settings_update(): void
    {
        // Profile update
        $profResponse = $this->actingAs($this->admin, 'admin')->put(route('admin.profile.update'), [
            'name'  => 'Commander Vance Updated',
            'email' => $this->admin->email,
        ]);
        $profResponse->assertSessionHas('success');
        $this->admin->refresh();
        $this->assertEquals('Commander Vance Updated', $this->admin->name);

        // Settings update
        $settingsResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.settings.update'));
        $settingsResponse->assertSessionHas('success');
    }

    public function test_admin_logout(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
}
