<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Investigation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class IncidentResponseService
{
    public function start(Incident $incident, User $investigator): Incident
    {
        if (! in_array($incident->status, ['VERIFIED', 'ESCALATED'], true)) {
            throw ValidationException::withMessages(['response' => 'Only verified or escalated incidents can enter response.']);
        }

        $class = config('spectral_response.classes.'.$investigator->responder_class, config('spectral_response.classes.D'));
        $maxAnomaly = $incident->anomaly_max_hp ?: config('spectral_response.anomaly_hp.'.$incident->severity);
        $minutes = (int) ceil(config('spectral_response.duration_minutes.'.$incident->severity) * $class['duration_multiplier']);

        $incident->fill([
            'anomaly_max_hp' => $maxAnomaly,
            'anomaly_hp' => $incident->anomaly_hp ?? $maxAnomaly,
            'investigator_max_hp' => $class['investigator_hp'],
            'investigator_hp' => $class['investigator_hp'],
            'response_investigator_id' => $investigator->id,
            'response_started_at' => now(),
            'response_deadline' => now()->addMinutes($minutes),
            'response_status' => 'ACTIVE',
            'status' => 'UNDER INVESTIGATION',
        ])->save();

        $this->log($incident, $investigator, 'Response started. Containment and stabilization procedure is active.', 'RESPONDING');
        return $incident;
    }

    public function progress(Incident $incident, User $investigator, int $containment, int $conditionCost, string $notes = ''): Incident
    {
        if ($incident->response_status !== 'ACTIVE') {
            throw ValidationException::withMessages(['response' => 'There is no active response to update.']);
        }
        if ($incident->response_investigator_id !== $investigator->id) {
            throw ValidationException::withMessages(['response' => 'Take over the response before recording progress.']);
        }

        $incident->anomaly_hp = max(0, $incident->anomaly_hp - $containment);
        $incident->investigator_hp = max(0, $incident->investigator_hp - $conditionCost);
        $incident->response_progress = min(100, (int) round((1 - $incident->anomaly_hp / $incident->anomaly_max_hp) * 100));

        if ($incident->investigator_hp === 0) {
            $incident->response_status = 'SUPPORT_REQUIRED';
            $incident->status = 'ESCALATED';
            $incident->support_requested_at = now();
            $this->log($incident, $investigator, $notes ?: 'Responder condition critical. Support or handover required; anomaly remains active.', 'SUPPORT_REQUIRED');
        } elseif ($incident->anomaly_hp === 0) {
            $incident->response_status = 'NEUTRALIZED';
            $incident->status = 'RESOLVED';
            $this->awardResolution($investigator);
            $this->log($incident, $investigator, $notes ?: 'Anomaly neutralized and incident resolved.', 'RESOLVED');
        } else {
            $this->log($incident, $investigator, $notes ?: 'Containment progress recorded.', 'MONITORING');
        }
        $incident->save();
        return $incident;
    }

    public function handover(Incident $incident, User $investigator): Incident
    {
        if (! in_array($incident->response_status, ['ACTIVE', 'SUPPORT_REQUIRED'], true)) {
            throw ValidationException::withMessages(['response' => 'This response cannot be handed over.']);
        }
        $class = config('spectral_response.classes.'.$investigator->responder_class, config('spectral_response.classes.D'));
        $incident->update([
            'response_investigator_id' => $investigator->id,
            'investigator_max_hp' => $class['investigator_hp'],
            'investigator_hp' => $class['investigator_hp'],
            'response_status' => 'ACTIVE',
            'status' => 'UNDER INVESTIGATION',
            'support_requested_at' => null,
        ]);
        $this->log($incident, $investigator, 'Response handover accepted. Stabilization continues.', 'RESPONDING');
        return $incident;
    }

    private function awardResolution(User $investigator): void
    {
        $investigator->increment('xp', config('spectral_response.xp.response_resolved'));
        $investigator->increment('successful_responses');
    }

    private function log(Incident $incident, User $investigator, string $notes, string $result): void
    {
        Investigation::create(['incident_id' => $incident->id, 'investigator_id' => $investigator->id, 'notes' => $notes, 'investigation_date' => now(), 'result' => $result]);
    }
}
