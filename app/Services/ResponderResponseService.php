<?php

namespace App\Services;

use App\Models\ResponderAssignment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResponderResponseService
{
    public function accept(ResponderAssignment $assignment, User $responder): ResponderAssignment
    {
        $this->guardResponder($assignment, $responder);
        if ($assignment->status !== 'ASSIGNED') {
            throw ValidationException::withMessages(['assignment' => 'Only a newly assigned case can be accepted.']);
        }
        $assignment->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);
        $responder->update(['responder_status' => 'BUSY']);
        return $assignment;
    }

    public function start(ResponderAssignment $assignment, User $responder): ResponderAssignment
    {
        $this->guardResponder($assignment, $responder);
        if (! in_array($assignment->status, ['ASSIGNED', 'ACCEPTED'], true)) {
            throw ValidationException::withMessages(['assignment' => 'Accept the assignment before starting the response.']);
        }

        // If not accepted yet, auto-accept when starting
        if ($assignment->status === 'ASSIGNED') {
            $assignment->accepted_at = now();
            $responder->update(['responder_status' => 'BUSY']);
        }

        $class = config('spectral_response.classes.' . ($responder->responder_class ?? 'D'), config('spectral_response.classes.D'));
        $severity = strtoupper($assignment->incident->severity);
        $maxAnomaly = config('spectral_response.anomaly_hp.' . $severity, 80);
        $durationSeconds = config('spectral_response.duration_seconds.' . $severity, 300);

        $assignment->update([
            'status'               => 'ACTIVE',
            'response_started_at'  => now(),
            'anomaly_hp'           => $maxAnomaly,
            'anomaly_max_hp'       => $maxAnomaly,
            'responder_hp'         => $class['responder_hp'] ?? 100,
            'responder_max_hp'     => $class['responder_hp'] ?? 100,
            'response_progress'    => 0,
            'response_deadline'    => now()->addSeconds($durationSeconds),
        ]);

        $assignment->incident->update([
            'status'          => 'UNDER INVESTIGATION',
            'response_status' => 'ACTIVE',
        ]);

        return $assignment;
    }

    public function syncRealtime(ResponderAssignment $assignment): ResponderAssignment
    {
        if ($assignment->status !== 'ACTIVE' || ! $assignment->response_started_at) {
            return $assignment;
        }

        $severity = strtoupper($assignment->incident->severity);
        $responder = $assignment->responder;
        $responderClass = $responder?->responder_class ?? 'D';

        $maxAnomaly = $assignment->anomaly_max_hp ?: config("spectral_response.anomaly_hp.{$severity}", 80);
        $maxResponder = $assignment->responder_max_hp ?: (config("spectral_response.classes.{$responderClass}.responder_hp") ?? 100);

        $elapsed = max(0, abs((int) now()->diffInSeconds($assignment->response_started_at, false)));

        $dps = config("spectral_response.dps.{$severity}", 0.25);
        $damageDealt = $elapsed * $dps;
        $currentAnomalyHp = max(0, (int) round($maxAnomaly - $damageDealt));

        // Responder takes damage per second matching the containment combat DPS,
        // so at full containment the remaining responder HP is exactly (ResponderMax - AnomalyMax).
        $finalExpectedHp = max(0, $maxResponder - $maxAnomaly);
        $currentResponderHp = max($finalExpectedHp, (int) round($maxResponder - $damageDealt));

        $progress = min(100, (int) round((1 - ($currentAnomalyHp / max(1, $maxAnomaly))) * 100));

        if ($currentAnomalyHp <= 0 || $progress >= 100) {
            if ($responder) {
                $assignment->responder_hp = $finalExpectedHp;
                $assignment->save();
                return $this->complete($assignment, $responder, 'Anomaly neutralized via real-time tactical containment.');
            }
        }

        $assignment->update([
            'anomaly_hp'        => $currentAnomalyHp,
            'responder_hp'      => $currentResponderHp,
            'response_progress' => $progress,
        ]);

        $assignment->incident->update([
            'anomaly_hp'        => $currentAnomalyHp,
            'response_progress' => $progress,
        ]);

        return $assignment;
    }

    public function progress(ResponderAssignment $assignment, User $responder, int $containment, int $conditionCost, string $notes = ''): ResponderAssignment
    {
        $this->guardResponder($assignment, $responder);
        if ($assignment->status !== 'ACTIVE') {
            throw ValidationException::withMessages(['assignment' => 'There is no active response to update.']);
        }

        $assignment->anomaly_hp = max(0, $assignment->anomaly_hp - $containment);
        $assignment->responder_hp = max(0, $assignment->responder_hp - $conditionCost);
        $assignment->response_progress = min(100, (int) round((1 - ($assignment->anomaly_hp / max(1, $assignment->anomaly_max_hp))) * 100));
        $assignment->notes = $notes ?: $assignment->notes;

        if ($assignment->responder_hp === 0) {
            $assignment->status = 'CRITICAL';
            $assignment->result = 'RESPONDER_CRITICAL';
            $responder->update(['responder_status' => 'CRITICAL']);
            $assignment->incident->update([
                'status'               => 'UNDER INVESTIGATION',
                'response_status'      => 'CRITICAL',
                'support_requested_at' => now(),
                'anomaly_hp'           => $assignment->anomaly_hp,
                'response_progress'    => $assignment->response_progress,
            ]);
        } elseif ($assignment->anomaly_hp === 0) {
            $assignment->status = 'COMPLETED';
            $assignment->result = 'NEUTRALIZED';
            $assignment->response_completed_at = now();
            $assignment->incident->update([
                'status'          => 'RESOLVED',
                'response_status' => 'NEUTRALIZED',
                'anomaly_hp'      => 0,
                'response_progress' => 100,
            ]);
            $responder->increment('xp', config('spectral_response.xp.response_resolved', 50));
            $responder->increment('successful_responses');
            $responder->update(['responder_status' => 'AVAILABLE']);
        } else {
            $assignment->incident->update([
                'anomaly_hp'        => $assignment->anomaly_hp,
                'response_progress' => $assignment->response_progress,
            ]);
        }

        $assignment->save();
        return $assignment;
    }

    public function complete(ResponderAssignment $assignment, User $responder, string $notes = ''): ResponderAssignment
    {
        $this->guardResponder($assignment, $responder);
        if ($assignment->status !== 'ACTIVE') {
            throw ValidationException::withMessages(['assignment' => 'Only an active response can be completed.']);
        }

        $severity = strtoupper($assignment->incident->severity);
        $responderClass = $responder->responder_class ?? 'D';
        $maxAnomaly = $assignment->anomaly_max_hp ?: config("spectral_response.anomaly_hp.{$severity}", 80);
        $maxResponder = $assignment->responder_max_hp ?: (config("spectral_response.classes.{$responderClass}.responder_hp") ?? 100);

        $assignment->anomaly_hp = 0;
        $assignment->responder_hp = max(0, $maxResponder - $maxAnomaly);
        $assignment->response_progress = 100;
        $assignment->status = 'COMPLETED';
        $assignment->result = 'NEUTRALIZED';
        $assignment->response_completed_at = now();
        $assignment->notes = $notes ?: ($assignment->notes ?: 'Anomaly successfully contained and neutralized by responder.');
        $assignment->save();

        $assignment->incident->update([
            'status'          => 'RESOLVED',
            'response_status' => 'NEUTRALIZED',
            'anomaly_hp'      => 0,
            'response_progress' => 100,
        ]);

        $responder->increment('xp', config('spectral_response.xp.response_resolved', 50));
        $responder->increment('successful_responses');
        $responder->update(['responder_status' => 'AVAILABLE']);

        return $assignment;
    }

    public function requestSupport(ResponderAssignment $assignment, User $responder, string $notes = ''): ResponderAssignment
    {
        $this->guardResponder($assignment, $responder);
        if (! in_array($assignment->status, ['ACCEPTED', 'ACTIVE'], true)) {
            throw ValidationException::withMessages(['assignment' => 'Cannot request support for this assignment state.']);
        }

        $assignment->status = 'CRITICAL';
        $assignment->result = 'SUPPORT_REQUESTED';
        $assignment->notes = $notes ?: ($assignment->notes ?: 'Responder requested tactical support and containment reinforcement.');
        $assignment->save();

        $responder->update(['responder_status' => 'CRITICAL']);
        $assignment->incident->update([
            'status'               => 'UNDER INVESTIGATION',
            'response_status'      => 'CRITICAL',
            'support_requested_at' => now(),
            'anomaly_hp'           => $assignment->anomaly_hp,
            'response_progress'    => $assignment->response_progress,
        ]);

        return $assignment;
    }

    private function guardResponder(ResponderAssignment $assignment, User $responder): void
    {
        if (! $responder->isResponder() || $assignment->responder_id !== $responder->id) {
            throw ValidationException::withMessages(['assignment' => 'You are not assigned to this response.']);
        }
    }
}
