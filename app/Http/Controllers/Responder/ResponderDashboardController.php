<?php

namespace App\Http\Controllers\Responder;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Resource;
use App\Models\ResponderAssignment;
use App\Models\WardStation;
use App\Services\ResponderResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponderDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();

        $newAssignments = ResponderAssignment::with(['incident.barangay', 'investigator'])
            ->where('responder_id', $user->id)
            ->where('status', 'ASSIGNED')
            ->latest('assigned_at')
            ->get();

        $activeResponses = ResponderAssignment::with(['incident.barangay', 'investigator'])
            ->where('responder_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'ACTIVE', 'SUPPORT_REQUIRED', 'CRITICAL'])
            ->latest('assigned_at')
            ->get();

        $completedResponses = ResponderAssignment::with(['incident.barangay', 'investigator'])
            ->where('responder_id', $user->id)
            ->where('status', 'COMPLETED')
            ->latest('response_completed_at')
            ->get();

        $classConfig = config('spectral_response.classes.' . ($user->responder_class ?? 'D'), config('spectral_response.classes.D'));
        $maxHp = $classConfig['responder_hp'] ?? 100;

        $stats = [
            'new_assignments_count'   => $newAssignments->count(),
            'active_responses_count'  => $activeResponses->count(),
            'completed_count'         => $completedResponses->count(),
            'total_assigned'          => $newAssignments->count() + $activeResponses->count() + $completedResponses->count(),
            'current_class'           => $user->responder_class ?? 'D',
            'xp'                      => $user->xp ?? 0,
            'successful_responses'    => $user->successful_responses ?? 0,
            'responder_hp'            => $maxHp,
            'responder_status'        => $user->responder_status ?? 'AVAILABLE',
        ];

        return view('responder.dashboard', compact(
            'user',
            'newAssignments',
            'activeResponses',
            'completedResponses',
            'stats'
        ));
    }

    public function show(ResponderAssignment $assignment, ?ResponderResponseService $service = null)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        abort_unless($assignment->responder_id === $user->id, 403, 'Unauthorized access to assignment.');

        if ($assignment->status === 'ACTIVE') {
            $service = $service ?? app(ResponderResponseService::class);
            $service->syncRealtime($assignment);
            $assignment->refresh();
        }

        $assignment->load([
            'incident.barangay',
            'incident.evidence',
            'incident.investigations.investigator',
            'investigator',
        ]);

        $incident = $assignment->incident;

        // Calculate nearest Ward Station and Safe Zone based on coordinates
        $wardStations = WardStation::with('barangay')->get();
        $nearestWard = $wardStations->sortBy(function ($ward) use ($incident) {
            $latDiff = $ward->latitude - $incident->latitude;
            $lngDiff = $ward->longitude - $incident->longitude;
            return ($latDiff * $latDiff) + ($lngDiff * $lngDiff);
        })->first();

        $nearestSafeZone = $wardStations->where('status', 'active')->sortBy(function ($ward) use ($incident) {
            $latDiff = $ward->latitude - $incident->latitude;
            $lngDiff = $ward->longitude - $incident->longitude;
            return ($latDiff * $latDiff) + ($lngDiff * $lngDiff);
        })->first();

        $availableResources = Resource::with('barangay')->where('status', 'active')->take(4)->get();

        // Calculate display values for HP and time
        $classConfig = config('spectral_response.classes.' . ($user->responder_class ?? 'D'), config('spectral_response.classes.D'));
        $severity = strtoupper($incident->severity);
        $displayAnomalyMax = $assignment->anomaly_max_hp ?: config('spectral_response.anomaly_hp.' . $severity, 100);
        $displayAnomalyHp = $assignment->anomaly_hp !== null ? $assignment->anomaly_hp : $displayAnomalyMax;

        $displayResponderMax = $assignment->responder_max_hp ?: ($classConfig['responder_hp'] ?? 100);
        $displayResponderHp = $assignment->responder_hp !== null ? $assignment->responder_hp : $displayResponderMax;

        $remainingMinutes = 0;
        $remainingSeconds = 0;
        if ($assignment->status === 'ACTIVE' && $assignment->response_deadline) {
            $diff = max(0, now()->diffInSeconds($assignment->response_deadline, false));
            $remainingMinutes = intdiv($diff, 60);
            $remainingSeconds = $diff % 60;
        } else {
            $totalSeconds = config('spectral_response.duration_seconds.' . $severity, 300);
            $remainingMinutes = intdiv($totalSeconds, 60);
            $remainingSeconds = $totalSeconds % 60;
        }

        $timeDisplay = sprintf('%02d:%02d', $remainingMinutes, $remainingSeconds);

        // DPS metrics for real-time frontend (responder condition decreases per second at combat DPS)
        $dps = config("spectral_response.dps.{$severity}", 0.25);
        $responderDps = $dps;

        return view('responder.show', compact(
            'assignment',
            'incident',
            'nearestWard',
            'nearestSafeZone',
            'availableResources',
            'displayAnomalyHp',
            'displayAnomalyMax',
            'displayResponderHp',
            'displayResponderMax',
            'timeDisplay',
            'dps',
            'responderDps'
        ));
    }

    public function sync(ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        abort_unless($assignment->responder_id === $user->id, 403, 'Unauthorized access to assignment.');

        if ($assignment->status === 'ACTIVE') {
            $service->syncRealtime($assignment);
            $assignment->refresh();
        }

        $remainingSeconds = 0;
        if ($assignment->status === 'ACTIVE' && $assignment->response_deadline) {
            $remainingSeconds = max(0, now()->diffInSeconds($assignment->response_deadline, false));
        }

        return response()->json([
            'status'                 => $assignment->status,
            'anomaly_hp'             => $assignment->anomaly_hp,
            'anomaly_max_hp'         => $assignment->anomaly_max_hp,
            'responder_hp'           => $assignment->responder_hp,
            'responder_max_hp'       => $assignment->responder_max_hp,
            'response_progress'      => $assignment->response_progress,
            'time_remaining_seconds' => $remainingSeconds,
            'is_completed'           => $assignment->status === 'COMPLETED',
        ]);
    }

    public function showByIncident($incidentId)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        $assignment = ResponderAssignment::where('incident_id', $incidentId)
            ->where('responder_id', $user->id)
            ->latest('assigned_at')
            ->first();

        if (! $assignment) {
            $assignment = ResponderAssignment::where('incident_id', $incidentId)->latest('assigned_at')->first();
        }

        if (! $assignment) {
            return redirect()->route('responder.dashboard')->with('error', 'No assignment found for this incident.');
        }

        return $this->show($assignment);
    }

    public function accept(ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        $service->accept($assignment, $user);

        // Automatically open the response screen upon accepting
        return redirect()->route('responder.response.incident', $assignment->incident_id)
            ->with('success', 'Assignment accepted. Response screen opened.');
    }

    public function start(ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        $service->start($assignment, $user);

        return redirect()->route('responder.response.incident', $assignment->incident_id)
            ->with('success', 'Response initialized and containment countdown started.');
    }

    public function complete(Request $request, ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        $service->complete($assignment, $user, $request->input('notes', ''));

        return redirect()->route('responder.assignments.show', $assignment)
            ->with('success', 'Anomaly neutralized. Incident successfully resolved and XP awarded.');
    }

    public function requestSupport(Request $request, ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $user = Auth::guard('responder')->user() ?? Auth::user();
        $service->requestSupport($assignment, $user, $request->input('notes', ''));

        return redirect()->route('responder.assignments.show', $assignment)
            ->with('warning', 'Support requested. Containment flagged as critical.');
    }

    public function progress(Request $request, ResponderAssignment $assignment, ResponderResponseService $service)
    {
        $data = $request->validate([
            'containment'    => 'required|integer|min:0|max:1000',
            'condition_cost' => 'required|integer|min:0|max:1000',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $user = Auth::guard('responder')->user() ?? Auth::user();
        $service->progress($assignment, $user, $data['containment'], $data['condition_cost'], $data['notes'] ?? '');

        return redirect()->route('responder.assignments.show', $assignment)
            ->with('success', 'Response monitoring update recorded.');
    }
}
