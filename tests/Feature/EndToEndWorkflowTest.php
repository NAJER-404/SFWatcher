<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Incident;
use App\Models\ResponderAssignment;
use App\Models\User;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_reporter_investigator_responder_workflow_end_to_end(): void
    {
        $this->seed(SpectralSeeder::class);

        $reporter = User::where('role', 'reporter')->firstOrFail();
        $investigator = User::where('role', 'investigator')->firstOrFail();
        $responder = User::where('role', 'responder')->firstOrFail();
        $barangay = Barangay::where('name', 'Hubang')->first() ?? Barangay::first();

        $initialXp = $responder->xp;

        // 1. REPORTER SUBMITS INCIDENT
        $reportResponse = $this->actingAs($reporter, 'web')->post('/incidents', [
            'incident_type' => 'Spirit Activity',
            'title'         => 'Apparition at Hubang Market',
            'description'   => 'Luminescent entity observed near ward perimeter.',
            'barangay_id'   => $barangay->id,
            'latitude'      => 8.5310,
            'longitude'     => 125.9730,
            'incident_date' => now()->toDateTimeString(),
            'severity'      => 'MEDIUM',
        ]);

        $reportResponse->assertRedirect();
        $incident = Incident::where('title', 'Apparition at Hubang Market')->firstOrFail();
        $this->assertSame('PENDING', $incident->status);
        $this->assertSame($reporter->id, $incident->reported_by);

        // 2. INVESTIGATOR SEES IT & OPENS DETAILS / REVIEW PAGE
        $reviewPage = $this->actingAs($investigator, 'investigator')->get("/investigator/incidents/{$incident->id}/review");
        $reviewPage->assertOk();
        $reviewPage->assertSee($incident->incident_code);
        $reviewPage->assertSee('Apparition at Hubang Market');

        // 3. INVESTIGATOR INVESTIGATES, MARKS COMPLETE, SETS RESULT = CONFIRMED
        $investigateResponse = $this->actingAs($investigator, 'investigator')->put("/investigator/incidents/{$incident->id}", [
            'status'                 => 'VERIFIED',
            'severity'               => 'MEDIUM',
            'notes'                  => 'Field inspection confirmed active apparition anomaly.',
            'investigation_result'   => 'CONFIRMED',
            'complete_investigation' => 1,
        ]);

        $investigateResponse->assertRedirect();
        $incident->refresh();
        $this->assertSame('CONFIRMED', $incident->investigation_result);
        $this->assertNotNull($incident->investigation_completed_at);

        // 4. VERIFY SYSTEM AUTOMATICALLY CREATED A RESPONDER ASSIGNMENT
        $assignment = ResponderAssignment::where('incident_id', $incident->id)->firstOrFail();
        $this->assertSame($responder->id, $assignment->responder_id);
        $this->assertSame('ASSIGNED', $assignment->status);

        // Investigator re-visits review page and sees the assigned responder
        $updatedReviewPage = $this->actingAs($investigator, 'investigator')->get("/investigator/incidents/{$incident->id}/review");
        $updatedReviewPage->assertOk();
        $updatedReviewPage->assertSee('Assign Responder');
        $updatedReviewPage->assertSee('Currently Assigned');
        $updatedReviewPage->assertSee($responder->name);


        // 5. RESPONDER SEES ASSIGNMENT ON SEPARATE DASHBOARD
        $responderDashboard = $this->actingAs($responder, 'responder')->get('/responder/dashboard');
        $responderDashboard->assertOk();
        $responderDashboard->assertSee('NEW RESPONSE ASSIGNMENTS');
        $responderDashboard->assertSee($incident->incident_code);
        $responderDashboard->assertSee('Awaiting Acceptance');
        $responderDashboard->assertSee('ACCEPT ASSIGNMENT');

        // 6. RESPONDER ACCEPTS -> AUTOMATICALLY OPENS RESPONSE SCREEN
        $acceptResponse = $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/accept");
        $acceptResponse->assertRedirect(route('responder.response.incident', $assignment->incident_id));

        $assignment->refresh();
        $this->assertSame('ACCEPTED', $assignment->status);

        // 7. RESPONSE SCREEN VISIBLY SHOWS HP & MECHANICS
        $responseScreen = $this->actingAs($responder, 'responder')->get("/responder/response/{$incident->id}");
        $responseScreen->assertOk();
        $responseScreen->assertSee('ANOMALY CONDITION');
        $responseScreen->assertSee('RESPONDER CONDITION');
        $responseScreen->assertSee('RESPONSE PROGRESS');
        $responseScreen->assertSee('TIME REMAINING');
        $responseScreen->assertSee('START RESPONSE');

        // 8. RESPONDER STARTS RESPONSE
        $startResponse = $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/start");
        $startResponse->assertRedirect();

        $assignment->refresh();
        $this->assertSame('ACTIVE', $assignment->status);
        $this->assertNotNull($assignment->response_started_at);
        $this->assertNotNull($assignment->response_deadline);
        $this->assertGreaterThan(0, $assignment->anomaly_hp);
        $this->assertGreaterThan(0, $assignment->responder_hp);

        // Active screen shows operational buttons
        $activeScreen = $this->actingAs($responder, 'responder')->get("/responder/assignments/{$assignment->id}");
        $activeScreen->assertOk();
        $activeScreen->assertSee('COMPLETE RESPONSE');
        $activeScreen->assertSee('REQUEST SUPPORT');

        // 9. RESPONDER COMPLETES RESPONSE -> ANOMALY REACHES 0, INCIDENT RESOLVED, XP AWARDED
        $completeResponse = $this->actingAs($responder, 'responder')->post("/responder/assignments/{$assignment->id}/complete", [
            'notes' => 'Anomaly fully contained, neutralized, and ground secured.',
        ]);
        $completeResponse->assertRedirect();

        $assignment->refresh();
        $incident->refresh();
        $responder->refresh();

        $this->assertSame(0, $assignment->anomaly_hp);
        $this->assertSame(100, $assignment->response_progress);
        $this->assertSame('COMPLETED', $assignment->status);
        $this->assertSame('NEUTRALIZED', $assignment->result);
        $this->assertSame('RESOLVED', $incident->status);
        $this->assertGreaterThan($initialXp, $responder->xp);
        $this->assertSame('AVAILABLE', $responder->responder_status);

        // 10. REPORTER SEES UPDATED RESOLVED STATUS AND ANOMALY CONDITION
        $reporterView = $this->actingAs($reporter, 'web')->get("/incidents/{$incident->id}");
        $reporterView->assertOk();
        $reporterView->assertSee('RESOLVED');
        $reporterView->assertSee('ANOMALY CONDITION');
        $reporterView->assertSee('STATUS TIMELINE');
    }
}
