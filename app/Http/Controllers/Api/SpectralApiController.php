<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Investigation;
use App\Models\Resource;
use App\Models\User;
use App\Models\WardStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SpectralApiController extends Controller
{
    public function incidents(Request $request): JsonResponse
    {
        $query = Incident::with(['barangay', 'reporter', 'evidence', 'investigations'])
            ->orderByDesc('incident_date');

        if ($request->filled('severity') && $request->severity !== 'ALL') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        $incidents = $query->get();

        return response()->json([
            'success'   => true,
            'count'     => $incidents->count(),
            'incidents' => $incidents,
        ]);
    }

    public function showIncident($id): JsonResponse
    {
        $incident = Incident::with(['barangay', 'reporter', 'evidence', 'investigations.investigator'])
            ->find($id);

        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
        }

        return response()->json([
            'success'  => true,
            'incident' => $incident,
        ]);
    }

    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];

        $address = null;
        $barangayName = null;
        $municipality = 'San Francisco';
        $province = 'Agusan del Sur';
        $country = 'Philippines';

        try {
            $response = Http::withHeaders([
                'User-Agent'      => 'SpectraWatch/1.0 (contact@spectrawatch.ph)',
                'Accept-Language' => 'en',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'format'         => 'jsonv2',
                'lat'            => $lat,
                'lon'            => $lng,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];

                $barangayName = $addr['quarter']
                    ?? $addr['suburb']
                    ?? $addr['village']
                    ?? $addr['neighbourhood']
                    ?? $addr['city_district']
                    ?? null;

                $municipality = $addr['town']
                    ?? $addr['city']
                    ?? $addr['municipality']
                    ?? 'San Francisco';

                $province = $addr['state']
                    ?? $addr['province']
                    ?? 'Agusan del Sur';

                $country = $addr['country'] ?? 'Philippines';

                $road = $addr['road'] ?? null;

                $parts = array_filter([$road, $barangayName, $municipality, $province, $country]);
                $address = implode(', ', $parts);
            }
        } catch (\Throwable $e) {
            // Fallback handled below
        }

        // Fallback if Nominatim did not return a barangay or failed
        if (empty($barangayName) || empty($address)) {
            $nearest = Barangay::all()->sortBy(function ($b) use ($lat, $lng) {
                $dLat = deg2rad($b->latitude - $lat);
                $dLng = deg2rad($b->longitude - $lng);
                return sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($b->latitude)) * sin($dLng / 2) ** 2;
            })->first();

            $barangayName = $nearest ? $nearest->name : 'Hubang';
            $address = "Brgy. {$barangayName}, {$municipality}, {$province}, {$country}";
        }

        // Also find matching Barangay model if present
        $matchedBarangay = Barangay::where('name', 'LIKE', '%' . str_replace('Barangay ', '', $barangayName) . '%')
            ->orWhere('name', $barangayName)
            ->first();

        return response()->json([
            'success'       => true,
            'address'       => $address,
            'barangay'      => $barangayName,
            'barangay_id'   => $matchedBarangay ? $matchedBarangay->id : null,
            'barangay_name' => $matchedBarangay ? $matchedBarangay->name : $barangayName,
            'municipality'  => $municipality,
            'province'      => $province,
            'country'       => $country,
            'latitude'      => $lat,
            'longitude'     => $lng,
        ]);
    }

    public function storeIncident(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'incident_type' => 'required|string|max:100',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'barangay_id'   => 'nullable|exists:barangays,id',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'incident_date' => 'nullable|date',
            'severity'      => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'evidence'      => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $userId = \Illuminate\Support\Facades\Auth::id() ?? $request->user()?->id ?? \App\Models\User::where('role', 'reporter')->first()?->id ?? 1;

        $incident = Incident::create([
            'reported_by'   => $userId,
            'barangay_id'   => $validated['barangay_id'] ?? null,
            'incident_type' => $validated['incident_type'],
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'incident_date' => $validated['incident_date'] ?? now(),
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
                'description'  => 'API upload evidence',
                'uploaded_by'  => $userId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Incident reported successfully.',
            'data'    => $incident->load(['barangay', 'evidence', 'reporter']),
        ], 201);
    }

    public function updateIncidentStatus(Request $request, $id): JsonResponse
    {
        $incident = Incident::find($id);
        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
        }

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

        $warden = \Illuminate\Support\Facades\Auth::user();

        Investigation::create([
            'incident_id'        => $incident->id,
            'investigator_id'    => $warden->id,
            'notes'              => $validated['notes'] ?? 'Updated incident status to ' . $validated['status'],
            'investigation_date' => now(),
            'result'             => $validated['status'],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Status updated successfully.',
            'incident' => $incident->fresh(['barangay', 'evidence', 'investigations']),
        ]);
    }

    public function wardStations(): JsonResponse
    {
        $wards = WardStation::with('barangay')->get();
        return response()->json([
            'success' => true,
            'count'   => $wards->count(),
            'wards'   => $wards,
        ]);
    }

    public function resources(): JsonResponse
    {
        $resources = Resource::with('barangay')->get();
        return response()->json([
            'success'   => true,
            'count'     => $resources->count(),
            'resources' => $resources,
        ]);
    }

    public function barangays(): JsonResponse
    {
        $barangays = Barangay::orderBy('name')->get();
        return response()->json([
            'success'   => true,
            'count'     => $barangays->count(),
            'barangays' => $barangays,
        ]);
    }

    public function stats(): JsonResponse
    {
        $incidents = Incident::all();
        return response()->json([
            'success' => true,
            'stats'   => [
                'total_incidents'    => $incidents->count(),
                'active_incidents'   => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED', 'ESCALATED'])->count(),
                'investigating'      => $incidents->where('status', 'UNDER INVESTIGATION')->count(),
                'critical_incidents' => $incidents->where('severity', 'CRITICAL')->count(),
                'ward_stations'      => WardStation::count(),
                'resources'          => Resource::count(),
            ]
        ]);
    }
}
