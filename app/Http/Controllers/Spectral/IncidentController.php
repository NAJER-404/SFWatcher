<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::with(['barangay', 'reporter', 'evidence'])
            ->orderByDesc('incident_date');

        if ($request->filled('severity') && $request->severity !== 'ALL') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        $incidents = $query->paginate(15);
        $barangays = Barangay::orderBy('name')->get();

        return view('spectral.incidents.index', compact('incidents', 'barangays'));
    }

    public function create()
    {
        $barangays = Barangay::orderBy('name')->get();
        return view('spectral.incidents.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'incident_type' => 'required|string|max:100',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'barangay_id'   => 'nullable|exists:barangays,id',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'incident_date' => 'required|date',
            'severity'      => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'evidence'      => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // max 10MB
        ]);

        $user = User::first() ?? User::create([
            'name' => 'Field Scout',
            'email' => 'scout@ectonet.gov',
            'password' => bcrypt('password'),
            'role' => 'reporter',
        ]);

        $incident = Incident::create([
            'reported_by'   => $user->id,
            'barangay_id'   => $validated['barangay_id'] ?? null,
            'incident_type' => $validated['incident_type'],
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'incident_date' => $validated['incident_date'],
            'severity'      => $validated['severity'],
            'status'        => 'PENDING',
        ]);

        if ($request->hasFile('evidence')) {
            $path = $request->file('evidence')->store('evidence', 'public');
            IncidentEvidence::create([
                'incident_id'  => $incident->id,
                'file_path'    => $path,
                'file_name'    => $request->file('evidence')->getClientOriginalName(),
                'file_type'    => $request->file('evidence')->getMimeType(),
                'description'  => 'Initial field evidence upload',
                'uploaded_by'  => $user->id,
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
        $incident = Incident::with(['barangay', 'reporter', 'evidence', 'investigations.investigator'])
            ->findOrFail($id);

        return view('spectral.incidents.show', compact('incident'));
    }

    public function updateStatus(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        $validated = $request->validate([
            'status'   => 'required|in:PENDING,UNDER INVESTIGATION,VERIFIED,RESOLVED,ESCALATED',
            'severity' => 'nullable|in:LOW,MEDIUM,HIGH,CRITICAL',
            'notes'    => 'nullable|string',
        ]);

        $incident->status = $validated['status'];
        if (!empty($validated['severity'])) {
            $incident->severity = $validated['severity'];
        }
        if (!empty($validated['notes'])) {
            $incident->notes = $validated['notes'];
        }
        $incident->save();

        $warden = User::where('role', 'investigator')->first() ?? User::first();

        // Record investigation action
        Investigation::create([
            'incident_id'        => $incident->id,
            'investigator_id'    => $warden->id,
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
}
