<?php
namespace Tests\Feature;

use App\Models\Incident;
use App\Models\ResponderAssignment;
use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponderAssignmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_responder_has_a_separate_login_portal(): void
    {
        $this->seed(SpectralSeeder::class);
        $responder = User::where('role', 'responder')->firstOrFail();
        $this->get('/responder/login')->assertOk()->assertSee('RESPONDER PORTAL');
        $this->post('/responder/login', ['email' => $responder->email, 'password' => 'password'])
            ->assertRedirect(route('responder.dashboard'));
        $this->assertAuthenticatedAs($responder, 'responder');
    }

    public function test_confirmed_investigation_is_assigned_accepted_and_resolved_by_responder(): void
    {
        $this->seed(SpectralSeeder::class);
        $investigator = User::where('role', 'investigator')->firstOrFail();
        $responder = User::where('role', 'responder')->firstOrFail();
        $incident = Incident::where('status', 'PENDING')->firstOrFail();
        $incident->severity = 'LOW';
        $incident->save();

        // Investigator must use the 'investigator' guard
        $this->actingAs($investigator, 'investigator')
            ->put("/investigator/incidents/{$incident->id}", [
                'status'               => 'VERIFIED',
                'severity'             => 'LOW',
                'notes'                => 'Evidence verified.',
                'investigation_result' => 'CONFIRMED',
                'complete_investigation' => 1,
            ])->assertRedirect();

        $incident->refresh();
        $this->assertSame('CONFIRMED', $incident->investigation_result);

        $this->actingAs($investigator, 'investigator')
            ->post("/investigator/incidents/{$incident->id}/assign-responder", ['responder_id' => $responder->id])
            ->assertRedirect();

        $assignment = ResponderAssignment::firstOrFail();
        $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/accept")->assertRedirect();
        $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/start")->assertRedirect();
        $assignment->refresh();
        $this->assertSame('ACTIVE', $assignment->status);
        $this->actingAs($responder, 'responder')->get("/responder/assignments/{$assignment->id}")
            ->assertOk()->assertSee('ANOMALY CONDITION')->assertSee('RESPONDER CONDITION')->assertSee('RESPONSE PROGRESS')->assertSee('TIME REMAINING');
        $this->actingAs($responder, 'responder')->put("/responder/assignments/{$assignment->id}/progress", ['containment' => $assignment->anomaly_hp, 'condition_cost' => 0, 'notes' => 'Anomaly neutralized.'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('COMPLETED', $assignment->fresh()->status);
        $this->assertSame('RESOLVED', $incident->fresh()->status);
    }

    public function test_realtime_dps_sync_updates_anomaly_and_responder_hp_safely(): void
    {
        $this->seed(SpectralSeeder::class);
        $responder = User::where('role', 'responder')->firstOrFail();
        $incident = Incident::where('status', 'PENDING')->firstOrFail();
        $incident->severity = 'MEDIUM';
        $incident->save();

        $assignment = ResponderAssignment::create([
            'incident_id'     => $incident->id,
            'investigator_id' => User::where('role', 'investigator')->first()->id,
            'responder_id'    => $responder->id,
            'assigned_at'     => now(),
            'status'          => 'ASSIGNED',
        ]);

        $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/start")->assertRedirect();
        $assignment->refresh();
        $this->assertSame('ACTIVE', $assignment->status);
        $this->assertSame(80, $assignment->anomaly_hp);

        // Simulate 40 seconds elapsed in response combat
        $assignment->update([
            'response_started_at' => now()->subSeconds(40),
        ]);

        // Sync endpoint returns updated live metrics
        $syncResponse = $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/sync");
        $syncResponse->assertOk();
        $data = $syncResponse->json();

        // 80 HP - (40s * 0.25 DPS) = 70 HP
        $this->assertSame(70, $data['anomaly_hp']);
        // Responder HP: 100 HP - (40s * 0.25 DPS) = 90 HP
        $this->assertSame(90, $data['responder_hp']);
        $this->assertSame(13, $data['response_progress']);
        $this->assertFalse($data['is_completed']);

        // Check response page renders without Step Containment Adjustment
        $showPage = $this->actingAs($responder, 'responder')->get("/responder/assignments/{$assignment->id}");
        $showPage->assertOk();
        $showPage->assertDontSee('Step Containment Adjustment');
        $showPage->assertSee('LIVE TACTICAL COMBAT ENGAGED');

        // Check completion leaves exactly ResponderMax - AnomalyMax (100 - 80 = 20 HP remaining)
        $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/complete");
        $assignment->refresh();
        $this->assertSame(20, $assignment->responder_hp);
    }
}
