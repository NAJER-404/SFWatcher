<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use App\Services\IncidentResponseService;
use Database\Seeders\SpectralSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_incident_can_be_handed_over_and_neutralized(): void
    {
        $this->seed(SpectralSeeder::class);
        $incident = Incident::where('status', 'VERIFIED')->firstOrFail();
        $first = User::where('role', 'investigator')->firstOrFail();
        $second = User::factory()->create(['role' => 'investigator', 'responder_class' => 'C']);
        $service = app(IncidentResponseService::class);

        $service->start($incident, $first);
        $this->assertSame('ACTIVE', $incident->fresh()->response_status);

        $service->handover($incident->fresh(), $second);
        $this->assertSame($second->id, $incident->fresh()->response_investigator_id);

        $active = $incident->fresh();
        $service->progress($active, $second, $active->anomaly_hp, 0, 'Containment stabilized.');

        $incident->refresh();
        $second->refresh();
        $this->assertSame(0, $incident->anomaly_hp);
        $this->assertSame('NEUTRALIZED', $incident->response_status);
        $this->assertSame('RESOLVED', $incident->status);
        $this->assertSame(1, $second->successful_responses);
        $this->assertSame(config('spectral_response.xp.response_resolved'), $second->xp);
    }
}
