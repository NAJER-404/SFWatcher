<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminIncidentController extends Controller
{
    /**
     * All Incidents registry (all stages)
     */
    public function index(Request $request)
    {
        $query = Incident::with([
            'barangay',
            'reporter',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ]);

        // Search by title, code, description, or reporter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('reporter', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by Severity
        if ($severity = $request->input('severity')) {
            if ($severity !== 'ALL') {
                $query->where('severity', strtoupper($severity));
            }
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            if ($status === 'ARCHIVED') {
                $query->archivedFromMap();
            } elseif ($status !== 'ALL') {
                $query->where('status', strtoupper($status));
            }
        }

        // Filter by Date
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('incident_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('incident_date', '<=', $dateTo);
        }

        $incidents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statusCounts = [
            'total'               => Incident::count(),
            'pending'             => Incident::where('status', 'PENDING')->count(),
            'under_investigation' => Incident::where('status', 'UNDER INVESTIGATION')->count(),
            'verified'            => Incident::where('status', 'VERIFIED')->count(),
            'resolved'            => Incident::where('status', 'RESOLVED')->count(),
            'archived'            => Incident::archivedFromMap()->count(),
        ];

        return view('admin.incidents.index', compact('incidents', 'statusCounts'));
    }

    /**
     * Resolved Incidents view with map archiving controls
     */
    public function resolved(Request $request)
    {
        $query = Incident::with([
            'barangay',
            'reporter',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ])->where('status', 'RESOLVED');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($mapStatus = $request->input('map_status')) {
            if ($mapStatus === 'ACTIVE') {
                $query->activeOnMap();
            } elseif ($mapStatus === 'ARCHIVED') {
                $query->archivedFromMap();
            }
        }

        $incidents = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $activeMapCount = Incident::where('status', 'RESOLVED')->activeOnMap()->count();
        $archivedMapCount = Incident::where('status', 'RESOLVED')->archivedFromMap()->count();

        return view('admin.incidents.resolved', compact('incidents', 'activeMapCount', 'archivedMapCount'));
    }

    /**
     * Archived Incidents view
     */
    public function archived(Request $request)
    {
        $query = Incident::with([
            'barangay',
            'reporter',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ])->archivedFromMap();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $incidents = $query->orderByDesc('archived_from_map_at')->paginate(20)->withQueryString();
        $archivedCount = Incident::archivedFromMap()->count();

        return view('admin.incidents.archived', compact('incidents', 'archivedCount'));
    }

    /**
     * Detailed Incident Inspector
     */
    public function show(Incident $incident)
    {
        $incident->load([
            'barangay',
            'reporter',
            'evidence',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ]);

        $adminLogs = AdminActivityLog::where('subject_type', 'Incident')
            ->where('subject_id', $incident->id)
            ->latest()
            ->get();

        return view('admin.incidents.show', compact('incident', 'adminLogs'));
    }

    /**
     * Delete incident and its associated records
     */
    public function destroy(Incident $incident)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();
        $code = $incident->incident_code;
        $title = $incident->title;

        DB::transaction(function () use ($incident, $admin, $code, $title) {
            $incident->evidence()->delete();
            $incident->investigations()->delete();
            $incident->responderAssignments()->delete();
            $incident->delete();

            AdminActivityLog::create([
                'user_id'      => $admin->id,
                'action'       => 'INCIDENT_DELETED',
                'subject_type' => 'Incident',
                'subject_id'   => null,
                'description'  => "Deleted incident record {$code} ({$title}) and all attached data.",
                'metadata'     => ['incident_code' => $code, 'title' => $title],
            ]);
        });

        return redirect()->route('admin.incidents.index')->with('success', "Incident {$code} has been successfully deleted.");
    }

    /**
     * Archive Resolved Incident from Active Map
     */
    public function archive(Request $request, Incident $incident)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        // Restriction check: Only resolved incidents can be archived
        if ($incident->status !== 'RESOLVED') {
            return back()->withErrors(['archive' => "Operation Denied: Incident {$incident->incident_code} is currently {$incident->status}. Only RESOLVED incidents can be removed from the active map."]);
        }

        if ($incident->isArchivedFromMap()) {
            return back()->with('info', "Incident {$incident->incident_code} is already archived from the active map.");
        }

        DB::transaction(function () use ($incident, $admin, $request) {
            $incident->archived_from_map_at = now();
            $incident->archived_by = $admin->id;
            $incident->archive_notes = $request->input('archive_notes', 'Archived from active tactical map by Administrator.');
            $incident->save();

            AdminActivityLog::create([
                'user_id'      => $admin->id,
                'action'       => 'INCIDENT_ARCHIVED',
                'subject_type' => 'Incident',
                'subject_id'   => $incident->id,
                'description'  => "Removed resolved incident {$incident->incident_code} ({$incident->title}) from active GIS map. Record and transcript preserved.",
                'metadata'     => [
                    'incident_code' => $incident->incident_code,
                    'notes'         => $incident->archive_notes,
                ],
            ]);
        });

        return back()->with('success', "Incident {$incident->incident_code} removed from active map. It is now hidden on Reporter and Investigator maps while preserved in Admin History.");
    }

    /**
     * Restore Archived Incident to Active Map
     */
    public function restore(Request $request, Incident $incident)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        if (! $incident->isArchivedFromMap()) {
            return back()->with('info', "Incident {$incident->incident_code} is already visible on the active map.");
        }

        DB::transaction(function () use ($incident, $admin) {
            $incident->archived_from_map_at = null;
            $incident->archived_by = null;
            $incident->archive_notes = null;
            $incident->save();

            AdminActivityLog::create([
                'user_id'      => $admin->id,
                'action'       => 'INCIDENT_RESTORED',
                'subject_type' => 'Incident',
                'subject_id'   => $incident->id,
                'description'  => "Restored incident {$incident->incident_code} to active GIS map with original RESOLVED status.",
                'metadata'     => [
                    'incident_code' => $incident->incident_code,
                ],
            ]);
        });

        return back()->with('success', "Incident {$incident->incident_code} restored to active map display with original RESOLVED status.");
    }

    /**
     * Incident Transcripts list & search
     */
    public function transcripts(Request $request)
    {
        $query = Incident::with([
            'barangay',
            'reporter',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('status', strtoupper($status));
            }
        }

        $incidents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.incidents.transcripts', compact('incidents'));
    }

    /**
     * Complete Historical Timeline / Transcript for an Incident
     */
    public function transcript(Incident $incident)
    {
        $incident->load([
            'barangay',
            'reporter',
            'evidence',
            'investigations.investigator',
            'responderAssignments.responder',
            'archivedBy',
        ]);

        // Build chronological timeline events
        $events = collect();

        // 1. Reported
        if ($incident->created_at) {
            $events->push([
                'stage'       => 'REPORTED',
                'title'       => 'Incident Reported to Defense Network',
                'timestamp'   => $incident->incident_date ?? $incident->created_at,
                'actor'       => $incident->reporter?->name ?? 'Civilian Field Scout',
                'role'        => 'Reporter',
                'description' => "Initial report filed for {$incident->incident_type}: \"{$incident->title}\" in Brgy. " . ($incident->barangay?->name ?? 'San Francisco'),
                'details'     => "Coordinates: {$incident->latitude}° N, {$incident->longitude}° E. Initial severity: {$incident->severity}.",
            ]);
        }

        // 2. Evidence Uploads
        foreach ($incident->evidence as $ev) {
            $events->push([
                'stage'       => 'EVIDENCE',
                'title'       => 'Field Evidence Submitted',
                'timestamp'   => $ev->created_at,
                'actor'       => $ev->uploader?->name ?? $incident->reporter?->name ?? 'Field Scout',
                'role'        => 'Evidence',
                'description' => "Uploaded {$ev->file_name} ({$ev->file_type})",
                'details'     => $ev->description ?: 'Sensor snapshot / visual telemetry registered.',
            ]);
        }

        // 3. Investigations
        foreach ($incident->investigations as $inv) {
            $events->push([
                'stage'       => 'INVESTIGATION',
                'title'       => "Field Investigation Logged [Result: {$inv->result}]",
                'timestamp'   => $inv->investigation_date ?? $inv->created_at,
                'actor'       => $inv->investigator?->name ?? 'Investigator',
                'role'        => 'Investigator',
                'description' => $inv->notes ?: 'Investigator conducted anomaly scan and threat verification.',
                'details'     => "Status at assessment: {$inv->result}",
            ]);
        }

        // 4. Responder Assignments & Execution
        foreach ($incident->responderAssignments as $assign) {
            $respName = $assign->responder?->name ?? 'Responder';
            $invName = $assign->investigator?->name ?? 'Investigator';

            if ($assign->assigned_at) {
                $events->push([
                    'stage'       => 'DISPATCH',
                    'title'       => "Responder Assignment Dispatched",
                    'timestamp'   => $assign->assigned_at,
                    'actor'       => $invName,
                    'role'        => 'Investigator',
                    'description' => "Assigned {$respName} (Class " . ($assign->responder?->responder_class ?? 'D') . ") for containment mission.",
                    'details'     => "Target Anomaly HP: {$assign->anomaly_max_hp} HP. Initial Responder HP: {$assign->responder_max_hp} HP.",
                ]);
            }

            if ($assign->accepted_at) {
                $events->push([
                    'stage'       => 'ACCEPTED',
                    'title'       => "Assignment Accepted by Responder",
                    'timestamp'   => $assign->accepted_at,
                    'actor'       => $respName,
                    'role'        => 'Responder',
                    'description' => "{$respName} accepted deployment orders and confirmed readiness.",
                    'details'     => "Status changed to ACCEPTED.",
                ]);
            }

            if ($assign->response_started_at) {
                $events->push([
                    'stage'       => 'RESPONSE_STARTED',
                    'title'       => "Active Containment Operation Commenced",
                    'timestamp'   => $assign->response_started_at,
                    'actor'       => $respName,
                    'role'        => 'Responder',
                    'description' => "Direct neutralization and ward stabilization started.",
                    'details'     => "Deadline: " . ($assign->response_deadline ? $assign->response_deadline->format('Y-m-d H:i:s') : 'N/A'),
                ]);
            }

            if ($assign->response_completed_at) {
                $events->push([
                    'stage'       => 'RESPONSE_COMPLETED',
                    'title'       => "Containment Completed [Result: " . ($assign->result ?? 'COMPLETED') . "]",
                    'timestamp'   => $assign->response_completed_at,
                    'actor'       => $respName,
                    'role'        => 'Responder',
                    'description' => $assign->notes ?: "Anomaly neutralized successfully. Containment seals verified.",
                    'details'     => "Final Anomaly HP: {$assign->anomaly_hp}/{$assign->anomaly_max_hp}. Final Responder HP: {$assign->responder_hp}/{$assign->responder_max_hp}.",
                ]);
            }
        }

        // 5. Resolution
        if ($incident->status === 'RESOLVED') {
            $events->push([
                'stage'       => 'RESOLVED',
                'title'       => 'Incident Officially Marked RESOLVED',
                'timestamp'   => $incident->investigation_completed_at ?? $incident->updated_at,
                'actor'       => 'Defense Command',
                'role'        => 'Command',
                'description' => 'Threat neutralized. Post-mission review completed.',
                'details'     => 'Incident placed in resolved registry.',
            ]);
        }

        // 6. Archiving / Restoring logs from AdminActivityLog
        $adminLogs = AdminActivityLog::where('subject_type', 'Incident')
            ->where('subject_id', $incident->id)
            ->get();

        foreach ($adminLogs as $log) {
            $events->push([
                'stage'       => $log->action,
                'title'       => $log->action === 'INCIDENT_ARCHIVED' ? 'Incident Archived from Active Map' : 'Incident Restored to Active Map',
                'timestamp'   => $log->created_at,
                'actor'       => $log->user?->name ?? 'Administrator',
                'role'        => 'Admin',
                'description' => $log->description,
                'details'     => $incident->archive_notes ?: 'Map visibility toggled by Administrator.',
            ]);
        }

        $events = $events->sortBy('timestamp')->values();

        return view('admin.incidents.transcript_show', compact('incident', 'events'));
    }
}
