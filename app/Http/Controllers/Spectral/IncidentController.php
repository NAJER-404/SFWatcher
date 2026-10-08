<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Incident::with(['barangay', 'reporter', 'evidence']);

        if ($request->filled('severity') && $request->severity !== 'ALL') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        $query->orderByRaw("CASE 
            WHEN status = 'PENDING' THEN 1 
            WHEN status = 'UNDER INVESTIGATION' THEN 2 
            WHEN status = 'VERIFIED' THEN 3 
            WHEN status = 'RESOLVED' THEN 4 
            ELSE 99 END ASC")
            ->orderByDesc('created_at');

        $incidents = $query->paginate(15)->withQueryString();
        $barangays = Barangay::orderBy('name')->get();

        $allIncidents     = Incident::all();
        $myIncidentsCount = $userId ? $allIncidents->where('reported_by', $userId)->count() : 0;

        $stats = [
            'total_incidents'    => $allIncidents->count(),
            'active_incidents'   => $allIncidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'investigating'      => $allIncidents->where('status', 'UNDER INVESTIGATION')->count(),
            'critical_incidents' => $allIncidents->where('severity', 'CRITICAL')->count(),
            'pending'            => $allIncidents->where('status', 'PENDING')->count(),
            'resolved'           => $allIncidents->where('status', 'RESOLVED')->count(),
            'my_reports'         => $myIncidentsCount,
        ];

        return view('spectral.incidents.index', compact('incidents', 'barangays', 'stats'));
    }

    public function create()
    {
        $barangays = Barangay::orderBy('name')->get();
        return view('spectral.incidents.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        // 1. Resolve / sanitize barangay_id if passed as name or missing
        $rawBarangay = $request->input('barangay_id');
        if (empty($rawBarangay) || !is_numeric($rawBarangay) || in_array($rawBarangay, ['undefined', 'null', ''])) {
            $nameCandidate = (is_string($rawBarangay) && !in_array($rawBarangay, ['undefined', 'null', '']))
                ? $rawBarangay
                : $request->input('barangay');

            $matched = null;
            if ($nameCandidate) {
                $matched = Barangay::where('name', $nameCandidate)
                    ->orWhere('name', 'LIKE', '%' . str_replace('Barangay ', '', $nameCandidate) . '%')
                    ->first();
            }

            if (!$matched && $request->filled('latitude') && $request->filled('longitude')) {
                $matched = \App\Http\Controllers\Api\SpectralApiController::resolveSanFranciscoBarangay(
                    (float) $request->latitude,
                    (float) $request->longitude
                );
            }

            $request->merge(['barangay_id' => $matched?->id]);
        }

        $validated = $request->validate([
            'incident_type' => 'required|string|max:100',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'barangay_id'   => 'nullable|exists:barangays,id',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'incident_date' => 'nullable',
            'severity'      => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'evidence'      => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // max 10MB
        ]);

        $userId = \Illuminate\Support\Facades\Auth::id()
            ?? \Illuminate\Support\Facades\Auth::guard('web')->id()
            ?? \Illuminate\Support\Facades\Auth::guard('admin')->id()
            ?? \Illuminate\Support\Facades\Auth::guard('investigator')->id()
            ?? \Illuminate\Support\Facades\Auth::guard('responder')->id();

        if (!$userId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and log in again.',
                ], 401);
            }
            abort(401, 'Unauthenticated.');
        }

        // Parse date safely
        $incidentDate = now();
        if (!empty($validated['incident_date'])) {
            try {
                $incidentDate = \Carbon\Carbon::parse($validated['incident_date']);
            } catch (\Throwable $e) {
                $incidentDate = now();
            }
        }

        // Ensure barangay_id is assigned
        $barangayId = $validated['barangay_id'] ?? null;
        if (!$barangayId) {
            $nearest = \App\Http\Controllers\Api\SpectralApiController::resolveSanFranciscoBarangay(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
            $barangayId = $nearest?->id;
        }

        try {
            $incident = Incident::create([
                'reported_by'   => $userId,
                'barangay_id'   => $barangayId,
                'incident_type' => $validated['incident_type'],
                'title'         => $validated['title'],
                'description'   => $validated['description'],
                'latitude'      => $validated['latitude'],
                'longitude'     => $validated['longitude'],
                'incident_date' => $incidentDate,
                'severity'      => $validated['severity'],
                'status'        => 'PENDING',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'incident_code') || $e->getCode() == 23505) {
                $incident = Incident::create([
                    'incident_code' => Incident::generateUniqueIncidentCode(),
                    'reported_by'   => $userId,
                    'barangay_id'   => $barangayId,
                    'incident_type' => $validated['incident_type'],
                    'title'         => $validated['title'],
                    'description'   => $validated['description'],
                    'latitude'      => $validated['latitude'],
                    'longitude'     => $validated['longitude'],
                    'incident_date' => $incidentDate,
                    'severity'      => $validated['severity'],
                    'status'        => 'PENDING',
                ]);
            } else {
                throw $e;
            }
        }

        if ($request->hasFile('evidence')) {
            $path = $request->file('evidence')->store('evidence', 'public');
            IncidentEvidence::create([
                'incident_id'  => $incident->id,
                'file_path'    => $path,
                'file_name'    => $request->file('evidence')->getClientOriginalName(),
                'file_type'    => $request->file('evidence')->getMimeType(),
                'description'  => 'Initial field evidence upload',
                'uploaded_by'  => $userId,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supernatural incident reported successfully.',
                'data'    => $incident->load(['barangay', 'evidence', 'reporter']),
            ]);
        }

        return redirect()->route('spectral.incidents.show', $incident->id)
            ->with('success', 'Incident ' . $incident->incident_code . ' submitted successfully to the San Francisco Node.');
    }

    public function show($id)
    {
        $incident = Incident::with(['barangay', 'reporter', 'evidence', 'investigations.investigator', 'responderAssignments.responder'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id)->orWhere('incident_code', $id);
                } else {
                    $q->where('incident_code', $id);
                }
            })
            ->firstOrFail();

        return view('spectral.incidents.show', compact('incident'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isReporter()) {
            abort(403, 'You are not authorized to update incident status.');
        }

        $incident = Incident::findOrFail($id);

        $validated = $request->validate([
            'status'   => 'required|in:PENDING,UNDER INVESTIGATION,VERIFIED,RESOLVED',
            'severity' => 'nullable|in:LOW,MEDIUM,HIGH,CRITICAL',
            'notes'    => 'nullable|string',
        ]);

        // Guard: do nothing if the status is unchanged (prevents re-verifying/re-submitting same status)
        if ($incident->status === $validated['status']) {
            return redirect()->back()->with('success', 'No change — incident is already ' . $incident->status . '.');
        }

        $incident->status = $validated['status'];
        if (!empty($validated['severity'])) {
            $incident->severity = $validated['severity'];
        }
        if (!empty($validated['notes'])) {
            $incident->notes = $validated['notes'];
        }
        $incident->save();

        $investigatorId = \Illuminate\Support\Facades\Auth::id();

        // Record investigation action
        Investigation::create([
            'incident_id'        => $incident->id,
            'investigator_id'    => $investigatorId,
            'notes'              => $validated['notes'] ?? 'Updated incident status to ' . $validated['status'],
            'investigation_date' => now(),
            'result'             => $validated['status'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Incident status updated successfully.',
                'data'    => $incident->fresh(['barangay', 'evidence', 'investigations']),
            ]);
        }

        return redirect()->back()->with('success', 'Investigation status updated for ' . $incident->incident_code);
    }

    public function destroy($id)
    {
        $incident = Incident::with(['evidence', 'investigations', 'responderAssignments'])->findOrFail($id);

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($incident->reported_by !== $user->id && !$user->isAdmin()) {
            abort(403, 'You are not authorized to delete this incident report.');
        }

        // Delete evidence files from storage
        foreach ($incident->evidence as $evidence) {
            if ($evidence->file_path && !str_starts_with($evidence->file_path, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_path);
            }
            $evidence->delete();
        }

        $incident->investigations()->delete();
        $incident->responderAssignments()->delete();

        // Clean from session read notifications if present
        $readNotifs = session('read_notifications', []);
        if (($key = array_search($incident->id, $readNotifs)) !== false) {
            unset($readNotifs[$key]);
            session(['read_notifications' => array_values($readNotifs)]);
        }

        $code = $incident->incident_code;
        $incident->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Incident {$code} has been permanently deleted from the system and GIS map.",
            ]);
        }

        return redirect()->route('spectral.my-reports')
            ->with('success', "Incident {$code} was permanently deleted from the system and GIS map.");
    }
}
