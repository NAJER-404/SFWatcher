<?php

namespace App\Http\Controllers\Investigator;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Investigation;
use App\Models\Resource;
use App\Models\WardStation;
use App\Models\User;
use App\Models\ResponderAssignment;
use App\Services\IncidentResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestigatorDashboardController extends Controller
{
    /**
     * Display the primary Investigator Dashboard.
     */
    public function index()
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responseInvestigator', 'responderAssignments.responder'])
            ->activeOnMap()
            ->orderByDesc('created_at')
            ->get();

        $wardStations = WardStation::with('barangay')->get();
        $resources    = Resource::with('barangay')->get();
        $barangays    = Barangay::orderBy('name')->get();

        // Real Database Statistics (no hardcoded numbers)
        $stats = [
            'active_incidents'    => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'pending_review'      => $incidents->where('status', 'PENDING')->count(),
            'under_investigation' => $incidents->where('status', 'UNDER INVESTIGATION')->count(),
            'high_severity'       => $incidents->whereIn('severity', ['HIGH', 'CRITICAL'])->count(),
            'active_responses'    => $incidents->where('response_status', 'ACTIVE')->count(),
            'resolved'            => $incidents->where('status', 'RESOLVED')->count(),
        ];

        // Investigation Queue: incidents requiring investigator attention
        $queueIncidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responderAssignments.responder'])
            ->whereIn('status', ['PENDING', 'UNDER INVESTIGATION'])
            ->orderByRaw("CASE WHEN severity = 'CRITICAL' THEN 1 WHEN severity = 'HIGH' THEN 2 WHEN severity = 'MEDIUM' THEN 3 ELSE 4 END")
            ->orderByDesc('incident_date')
            ->take(8)
            ->get();

        return view('investigator.dashboard', compact(
            'incidents',
            'wardStations',
            'resources',
            'barangays',
            'stats',
            'queueIncidents'
        ));
    }

    /**
     * Dedicated Investigation Queue page.
     */
    public function queue(Request $request)
    {
        $query = Incident::with(['barangay', 'reporter', 'evidence', 'investigations'])
            ->whereIn('status', ['PENDING', 'UNDER INVESTIGATION']);

        if ($request->filled('severity') && $request->severity !== 'ALL') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('incident_code', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $incidents = $query->orderByRaw("CASE WHEN severity = 'CRITICAL' THEN 1 WHEN severity = 'HIGH' THEN 2 WHEN severity = 'MEDIUM' THEN 3 ELSE 4 END")
            ->orderByDesc('incident_date')
            ->paginate(15);

        $barangays = Barangay::orderBy('name')->get();

        return view('investigator.incidents.queue', compact('incidents', 'barangays'));
    }

    /**
     * All Incident Reports view for investigator.
     */
    public function incidents(Request $request)
    {
        $query = Incident::with(['barangay', 'reporter', 'evidence', 'investigations']);

        if ($request->filled('status')) {
            if ($request->status === 'ACTIVE') {
                $query->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED']);
            } elseif ($request->status !== 'ALL') {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('severity') && $request->severity !== 'ALL') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('incident_code', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $incidents = $query->orderByDesc('incident_date')->paginate(15);
        $barangays = Barangay::orderBy('name')->get();

        return view('investigator.incidents.index', compact('incidents', 'barangays'));
    }

    /**
     * Detailed Incident Review view.
     */
    public function review($id)
    {
        $incident = Incident::with(['barangay', 'reporter', 'evidence', 'investigations.investigator', 'responderAssignments.responder'])
            ->where('id', $id)
            ->orWhere('incident_code', $id)
            ->firstOrFail();

        $sev = strtoupper($incident->severity ?? 'MEDIUM');
        $eligibleClasses = config("spectral_response.severity_eligibility.{$sev}", ['D', 'C', 'B', 'A']);

        // Eligible responders for this incident's severity
        $availableResponders = User::where('role', 'responder')
            ->whereIn('responder_class', $eligibleClasses)
            ->orderByRaw("CASE WHEN responder_status = 'AVAILABLE' THEN 1 ELSE 2 END")
            ->orderBy('responder_class')
            ->get();

        $allResponders = User::where('role', 'responder')->get();

        return view('investigator.incidents.review', compact('incident', 'availableResponders', 'allResponders', 'eligibleClasses'));
    }

    /**
     * Update incident status, severity, and record investigation note.
     */
    public function updateIncident(Request $request, $id)
    {
        $incident = Incident::where('id', $id)
            ->orWhere('incident_code', $id)
            ->firstOrFail();

        $request->merge([
            'status'   => $request->input('status') ?? $incident->status,
            'severity' => $request->input('severity') ?? $incident->severity,
        ]);

        $validated = $request->validate([
            'status'       => 'required|in:PENDING,UNDER INVESTIGATION,VERIFIED,RESOLVED',
            'severity'     => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'notes'        => 'nullable|string',
            'responder_id' => 'nullable|exists:users,id',
        ]);

        // Guard: Cannot mark RESOLVED unless a responder has COMPLETED their assignment
        if ($validated['status'] === 'RESOLVED') {
            $hasCompletedAssignment = ResponderAssignment::where('incident_id', $incident->id)
                ->where('status', 'COMPLETED')
                ->exists();

            if (! $hasCompletedAssignment) {
                return back()->withErrors([
                    'status' => 'Cannot mark as Resolved — the assigned responder has not completed their response yet.'
                ]);
            }
        }

        // Guard: Prevent regressing workflow stages (e.g. from VERIFIED or RESOLVED back to earlier stages)
        $statusRank = [
            'PENDING'             => 1,
            'UNDER INVESTIGATION' => 2,
            'VERIFIED'            => 3,
            'RESOLVED'            => 4,
        ];
        $currentRank = $statusRank[$incident->status] ?? 1;
        $newRank     = $statusRank[$validated['status']] ?? 1;

        if ($newRank < $currentRank) {
            return back()->withErrors([
                'status' => "Cannot regress workflow stage from {$incident->status} to {$validated['status']}."
            ]);
        }

        $oldStatus   = $incident->status;
        $oldSeverity = $incident->severity;

        $newSeverity = strtoupper($validated['severity']);
        $incident->status   = $validated['status'];
        $incident->severity = $newSeverity;

        // Auto-determine Anomaly HP and Required Class from severity
        $anomalyMaxHp = config("spectral_response.anomaly_hp.{$newSeverity}", 80);
        $incident->anomaly_max_hp = $anomalyMaxHp;
        if ($incident->anomaly_hp === null || $oldSeverity !== $newSeverity) {
            $incident->anomaly_hp = $anomalyMaxHp;
        }

        $incident->required_responder_class = config("spectral_response.required_minimum_class.{$newSeverity}", 'D');

        $noteText = !empty($validated['notes'])
            ? trim($validated['notes'])
            : "Status updated from {$oldStatus} to {$validated['status']}" . ($oldSeverity !== $newSeverity ? " (Severity set to {$newSeverity} [Anomaly HP: {$anomalyMaxHp}])" : "");

        $incident->notes = $noteText;

        $investigatorId = Auth::guard('investigator')->id()
            ?? Auth::id()
            ?? (User::where('role', 'investigator')->first()?->id);

        $assignedResponderName = null;

        // When explicit responder_id is provided, create or update assignment
        if (!empty($validated['responder_id'])) {
            $explicitResponder = User::where('id', $validated['responder_id'])->where('role', 'responder')->first();
            if ($explicitResponder) {
                ResponderAssignment::updateOrCreate(
                    ['incident_id' => $incident->id],
                    [
                        'investigator_id' => $investigatorId,
                        'responder_id'    => $explicitResponder->id,
                        'assigned_at'     => now(),
                        'status'          => 'ASSIGNED',
                        'anomaly_hp'      => $incident->anomaly_hp,
                        'anomaly_max_hp'  => $incident->anomaly_max_hp,
                    ]
                );
                $assignedResponderName = $explicitResponder->name;
            }
        }

        // When status transitions to VERIFIED — auto-assign responder if none exists and an eligible one is available
        if ($validated['status'] === 'VERIFIED') {
            if (! $incident->investigation_result) {
                $incident->investigation_result = 'CONFIRMED';
            }
            if (! $incident->investigation_completed_at) {
                $incident->investigation_completed_at = now();
            }

            $existingAssignment = ResponderAssignment::where('incident_id', $incident->id)->first();
            if (! $existingAssignment) {
                $eligibleClasses = config("spectral_response.severity_eligibility.{$newSeverity}", ['D', 'C', 'B', 'A']);
                $availableResponder = User::where('role', 'responder')
                    ->where('responder_status', 'AVAILABLE')
                    ->whereIn('responder_class', $eligibleClasses)
                    ->orderBy('id')
                    ->first();

                ResponderAssignment::create([
                    'incident_id'     => $incident->id,
                    'investigator_id' => $investigatorId,
                    'responder_id'    => $availableResponder?->id,
                    'assigned_at'     => now(),
                    'status'          => $availableResponder ? 'ASSIGNED' : 'WAITING',
                    'anomaly_hp'      => $incident->anomaly_hp,
                    'anomaly_max_hp'  => $incident->anomaly_max_hp,
                ]);

                if ($availableResponder) {
                    $assignedResponderName = $availableResponder->name;
                }
            }
        }

        $incident->save();

        // Record in investigations history
        Investigation::create([
            'incident_id'        => $incident->id,
            'investigator_id'    => $investigatorId,
            'notes'              => $noteText,
            'investigation_date' => now(),
            'result'             => $validated['status'],
            'completed_at'       => in_array($validated['status'], ['RESOLVED', 'VERIFIED']) ? now() : null,
        ]);

        // Audit Trail Cap: keep only 6 newest records per incident, delete older ones
        $excessIds = Investigation::where('incident_id', $incident->id)
            ->orderByDesc('investigation_date')
            ->orderByDesc('id')
            ->skip(6)
            ->take(100)
            ->pluck('id');

        if ($excessIds->isNotEmpty()) {
            Investigation::whereIn('id', $excessIds)->delete();
        }

        $investigator = Auth::guard('investigator')->user() ?? Auth::user();
        if ($investigator) {
            $investigator->increment('investigations_completed');
            $investigator->increment('xp', config('spectral_response.xp.investigation_completed', 15));
        }

        $successMsg = $assignedResponderName
            ? "Incident {$incident->incident_code} successfully assigned to {$assignedResponderName}."
            : "Incident {$incident->incident_code} successfully updated to {$incident->status}.";

        return redirect()->back()->with('success', $successMsg);
    }

    public function assignResponder(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $data = $request->validate(['responder_id' => 'required|exists:users,id']);
        $responder = User::where('id', $data['responder_id'])->where('role', 'responder')->firstOrFail();

        // Server-side validation: Responder class must be eligible for the incident's severity
        $sev = strtoupper($incident->severity ?? 'MEDIUM');
        $eligibleClasses = config("spectral_response.severity_eligibility.{$sev}", ['D', 'C', 'B', 'A']);

        if (!in_array($responder->responder_class, $eligibleClasses)) {
            return back()->withErrors([
                'responder_id' => "Responder {$responder->name} (Class {$responder->responder_class}) is not eligible for {$sev} severity anomaly (Requires Class " . implode('/', $eligibleClasses) . ")."
            ]);
        }

        if (! $incident->investigation_completed_at) {
            $incident->investigation_completed_at = now();
        }
        $incident->investigation_result = 'CONFIRMED';
        $incident->status = 'VERIFIED';
        $incident->save();

        $investigatorId = Auth::guard('investigator')->id() ?? Auth::id() ?? (User::where('role', 'investigator')->first()?->id);

        ResponderAssignment::updateOrCreate(
            ['incident_id' => $incident->id],
            [
                'investigator_id' => $investigatorId,
                'responder_id'    => $responder->id,
                'assigned_at'     => now(),
                'status'          => 'ASSIGNED',
                'anomaly_hp'      => $incident->anomaly_hp,
                'anomaly_max_hp'  => $incident->anomaly_max_hp,
            ]
        );

        return back()->with('success', "Assignment created successfully. Responder {$responder->name} (Class {$responder->responder_class}) assigned to {$incident->incident_code}.");
    }

    /**
     * Return eligible responders for an incident as JSON (for AJAX modal).
     */
    public function eligibleResponders($id)
    {
        $incident = Incident::findOrFail($id);
        $sev = strtoupper($incident->severity ?? 'MEDIUM');
        $eligibleClasses = config("spectral_response.severity_eligibility.{$sev}", ['D', 'C', 'B', 'A']);

        $classHpMap = [
            'D' => 100,
            'C' => 120,
            'B' => 145,
            'A' => 170,
        ];

        $responders = User::where('role', 'responder')
            ->whereIn('responder_class', $eligibleClasses)
            ->orderByRaw("CASE WHEN responder_status = 'AVAILABLE' THEN 1 ELSE 2 END")
            ->orderBy('responder_class')
            ->get(['id', 'name', 'responder_class', 'responder_status'])
            ->map(function ($resp) use ($classHpMap) {
                return [
                    'id'               => $resp->id,
                    'name'             => $resp->name,
                    'responder_class'  => $resp->responder_class,
                    'responder_status' => $resp->responder_status,
                    'hp'               => $classHpMap[$resp->responder_class] ?? 100,
                ];
            });

        $latestAssignment = ResponderAssignment::where('incident_id', $incident->id)->first();

        return response()->json([
            'responders'         => $responders,
            'eligibleClasses'    => $eligibleClasses,
            'severity'           => $sev,
            'anomalyMaxHp'       => $incident->anomaly_max_hp ?? config("spectral_response.anomaly_hp.{$sev}", 80),
            'currentResponderId' => $latestAssignment?->responder_id,
        ]);
    }

    /**
     * AJAX endpoint: assign a responder and set incident status.
     * Returns JSON for client-side UI update (no page reload).
     */
    public function assignResponderAjax(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $data = $request->validate([
            'responder_id' => 'required|exists:users,id',
            'workflow'     => 'nullable|string',
        ]);

        $responder = User::where('id', $data['responder_id'])->where('role', 'responder')->firstOrFail();

        // Server-side class eligibility check
        $sev = strtoupper($incident->severity ?? 'MEDIUM');
        $eligibleClasses = config("spectral_response.severity_eligibility.{$sev}", ['D', 'C', 'B', 'A']);

        if (!in_array($responder->responder_class, $eligibleClasses)) {
            return response()->json([
                'error' => "Responder {$responder->name} (Class {$responder->responder_class}) is not eligible for {$sev} severity (Requires Class " . implode('/', $eligibleClasses) . ")."
            ], 422);
        }

        $investigatorId = Auth::guard('investigator')->id()
            ?? Auth::id()
            ?? (User::where('role', 'investigator')->first()?->id);

        if (!$incident->investigation_completed_at) {
            $incident->investigation_completed_at = now();
        }
        $incident->investigation_result = 'CONFIRMED';
        $incident->save();

        // Create / update the responder assignment
        ResponderAssignment::updateOrCreate(
            ['incident_id' => $incident->id],
            [
                'investigator_id' => $investigatorId,
                'responder_id'    => $responder->id,
                'assigned_at'     => now(),
                'status'          => 'ASSIGNED',
                'anomaly_hp'      => $incident->anomaly_hp,
                'anomaly_max_hp'  => $incident->anomaly_max_hp,
            ]
        );

        // Audit trail
        Investigation::create([
            'incident_id'        => $incident->id,
            'investigator_id'    => $investigatorId,
            'notes'              => "Responder {$responder->name} (Class {$responder->responder_class}) assigned.",
            'investigation_date' => now(),
            'result'             => 'ASSIGNED',
            'completed_at'       => now(),
        ]);

        return response()->json([
            'success'        => true,
            'status'         => $incident->status,
            'responderId'    => $responder->id,
            'responderName'  => $responder->name,
            'responderClass' => $responder->responder_class,
            'message'        => "{$responder->name} (Class {$responder->responder_class}) assigned successfully.",
        ]);
    }

    /**
     * Reject (delete) an incident — investigator confirmation required.
     */
    public function rejectIncident(Request $request, $id)
    {
        $incident = Incident::where('id', $id)
            ->orWhere('incident_code', $id)
            ->firstOrFail();

        $code       = $incident->incident_code;
        $title      = $incident->title;
        $severity   = $incident->severity;
        $reporterId = $incident->reported_by;
        $rejectionReason = $request->input('notes') ?: 'Investigation report rejected by defense command.';

        // Dispatch rejection notification directly to civilian reporter
        if ($reporterId) {
            $existingNotifs = \Illuminate\Support\Facades\Cache::get("user_notifications_{$reporterId}", []);
            $existingNotifs[] = [
                'incident_code' => $code,
                'title'         => $title,
                'status'        => 'REJECTED',
                'severity'      => $severity,
                'investigator'  => Auth::guard('investigator')->user()?->name ?? 'Lead Investigator',
                'notes'         => 'Reason of rejection: ' . $rejectionReason,
                'updated_at'    => now()->toIso8601String(),
                'incident_id'   => 'rej_' . $incident->id,
                'is_read'       => false,
            ];
            \Illuminate\Support\Facades\Cache::forever("user_notifications_{$reporterId}", $existingNotifs);
        }

        $incident->delete();

        if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
            return response()->json([
                'success'  => true,
                'message'  => "Incident {$code} rejected and removed from defense maps.",
                'redirect' => route('investigator.dashboard'),
            ]);
        }

        return redirect()->route('investigator.dashboard')
            ->with('success', "Incident {$code} has been rejected and removed from the system.");
    }

    public function startResponse(Request $request, $id, IncidentResponseService $responses)
    {
        $incident = Incident::findOrFail($id);
        $responses->start($incident, Auth::guard('investigator')->user() ?? Auth::user());
        return back()->with('success', "Response started for {$incident->incident_code}.");
    }

    public function updateResponse(Request $request, $id, IncidentResponseService $responses)
    {
        $validated = $request->validate([
            'containment' => 'required|integer|min:0|max:100',
            'condition_cost' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);
        $incident = Incident::findOrFail($id);
        $responses->progress($incident, Auth::guard('investigator')->user() ?? Auth::user(), $validated['containment'], $validated['condition_cost'], $validated['notes'] ?? '');
        return back()->with('success', "Response update saved for {$incident->incident_code}.");
    }

    public function takeOverResponse(Request $request, $id, IncidentResponseService $responses)
    {
        $incident = Incident::findOrFail($id);
        $responses->handover($incident, Auth::guard('investigator')->user() ?? Auth::user());
        return back()->with('success', "You are now assigned to {$incident->incident_code}.");
    }

    /**
     * Dedicated Investigator Map view.
     */
    public function map()
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responderAssignments.responder'])
            ->activeOnMap()
            ->orderByDesc('created_at')
            ->get();

        $wardStations = WardStation::with('barangay')->get();
        $resources    = Resource::with('barangay')->get();
        $barangays    = Barangay::orderBy('name')->get();

        return view('investigator.map', compact('incidents', 'wardStations', 'resources', 'barangays'));
    }

    /**
     * Safe Zones response view.
     */
    public function safeZones()
    {
        $wardStations = WardStation::with('barangay')->where('status', 'active')->get();
        $barangays    = Barangay::orderBy('name')->get();

        return view('investigator.response.safe_zones', compact('wardStations', 'barangays'));
    }

    /**
     * Responders roster view.
     */
    public function responders()
    {
        $classConfig = config('spectral_response.classes', [
            'D' => ['responder_hp' => 100, 'effectiveness' => 10],
            'C' => ['responder_hp' => 115, 'effectiveness' => 12],
            'B' => ['responder_hp' => 130, 'effectiveness' => 15],
            'A' => ['responder_hp' => 170, 'effectiveness' => 18],
        ]);

        $responders = User::where('role', 'responder')
            ->with(['responderAssignments' => function ($q) {
                $q->whereIn('status', ['ASSIGNED', 'IN_PROGRESS'])->with('incident');
            }])
            ->orderByRaw("CASE WHEN responder_status = 'AVAILABLE' THEN 1 WHEN responder_status = 'ON_DUTY' THEN 2 ELSE 3 END")
            ->orderBy('responder_class')
            ->get();

        $stats = [
            'total'     => $responders->count(),
            'available' => $responders->where('responder_status', 'AVAILABLE')->count(),
            'on_duty'   => $responders->where('responder_status', 'ON_DUTY')->count(),
            'class_a'   => $responders->where('responder_class', 'A')->count(),
            'class_b'   => $responders->where('responder_class', 'B')->count(),
            'class_c'   => $responders->where('responder_class', 'C')->count(),
            'class_d'   => $responders->where('responder_class', 'D')->count(),
        ];

        return view('investigator.response.responders', compact('responders', 'classConfig', 'stats'));
    }

    /**
     * Ward Stations response view.
     */
    public function wards()
    {
        $wardStations = WardStation::with('barangay')->get();

        return view('investigator.response.ward_stations', compact('wardStations'));
    }

    /**
     * Resource Nodes response view.
     */
    public function resources()
    {
        $resources = Resource::with('barangay')->get();

        return view('investigator.response.resources', compact('resources'));
    }

    /**
     * Investigator Profile view.
     */
    public function profile()
    {
        $user = Auth::user();
        $recentInvestigations = Investigation::with('incident')
            ->where('investigator_id', $user->id)
            ->orderByDesc('investigation_date')
            ->take(10)
            ->get();

        return view('investigator.profile', compact('user', 'recentInvestigations'));
    }
}
