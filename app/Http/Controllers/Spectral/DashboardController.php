<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Resource;
use App\Models\WardStation;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responderAssignments'])
            ->orderByDesc('created_at')
            ->get();

        $wardStations = WardStation::with('barangay')->get();
        $resources    = Resource::with('barangay')->get();
        $barangays    = Barangay::orderBy('name')->get();
        $equipment    = Equipment::all();

        $userId = Auth::id();

        // Fetch current user's incidents with investigation history for the notification bell
        $myIncidents = Incident::with(['barangay', 'investigations.investigator'])
            ->where(function ($query) use ($userId) {
                $query->where('reported_by', $userId)
                      ->orWhereHas('evidence', function ($q) use ($userId) {
                          $q->where('uploaded_by', $userId);
                      });
            })
            ->orderByDesc('updated_at')
            ->get();

        $readNotifs = session('read_notifications', []);

        $notifications = $myIncidents
            ->filter(fn($inc) => $inc->investigations->isNotEmpty())
            ->map(function ($inc) use ($readNotifs) {
                $latest = $inc->investigations->sortByDesc('investigation_date')->first();
                return [
                    'incident_code' => $inc->incident_code,
                    'title'         => $inc->title,
                    'status'        => $inc->status,
                    'severity'      => $inc->severity,
                    'investigator'  => $latest?->investigator?->name ?? 'System',
                    'notes'         => $latest?->notes,
                    'updated_at'    => $latest?->investigation_date ?? $inc->updated_at,
                    'incident_id'   => $inc->id,
                    'is_read'       => in_array($inc->id, $readNotifs),
                ];
            })
            ->values()
            ->take(8);

        $unreadNotifCount = $notifications->where('is_read', false)->count();

        $stats = [
            'total_incidents'    => $incidents->count(),
            'active_incidents'   => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'investigating'      => $incidents->where('status', 'UNDER INVESTIGATION')->count(),
            'critical_incidents' => $incidents->where('severity', 'CRITICAL')->count(),
            'ward_stations'      => $wardStations->count(),
            'resources'          => $resources->count(),
            // User-specific stats
            'my_reports'         => $myIncidents->count(),
            'pending'            => $incidents->where('status', 'PENDING')->count(),
            'resolved'           => $incidents->where('status', 'RESOLVED')->count(),
            'notifications'      => $unreadNotifCount,
        ];

        return view('spectral.dashboard', compact(
            'incidents', 'wardStations', 'resources', 'barangays', 'equipment', 'stats', 'notifications'
        ));
    }

    public function myReports()
    {
        $userId       = Auth::id();
        $statusWeight = [
            'PENDING'             => 1,
            'UNDER INVESTIGATION' => 2,
            'VERIFIED'            => 3,
            'RESOLVED'            => 4,
        ];

        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations.investigator', 'responderAssignments'])
            ->where(function ($query) use ($userId) {
                $query->where('reported_by', $userId)
                      ->orWhereHas('evidence', function ($q) use ($userId) {
                          $q->where('uploaded_by', $userId);
                      });
            })
            ->get()
            ->sort(function ($a, $b) use ($statusWeight) {
                $wA = $statusWeight[$a->status] ?? 99;
                $wB = $statusWeight[$b->status] ?? 99;
                if ($wA === $wB) {
                    return $b->created_at <=> $a->created_at;
                }
                return $wA <=> $wB;
            })
            ->values();

        $allIncidents = Incident::all();
        $readNotifs   = session('read_notifications', []);

        // Build notifications for the header bell on this page too
        $notifications = $incidents
            ->filter(fn($inc) => $inc->investigations->isNotEmpty())
            ->map(function ($inc) use ($readNotifs) {
                $latest = $inc->investigations->sortByDesc('investigation_date')->first();
                return [
                    'incident_code' => $inc->incident_code,
                    'title'         => $inc->title,
                    'status'        => $inc->status,
                    'severity'      => $inc->severity,
                    'investigator'  => $latest?->investigator?->name ?? 'System',
                    'notes'         => $latest?->notes,
                    'updated_at'    => $latest?->investigation_date ?? $inc->updated_at,
                    'incident_id'   => $inc->id,
                    'is_read'       => in_array($inc->id, $readNotifs),
                ];
            })
            ->values()
            ->take(8);

        $unreadNotifCount = $notifications->where('is_read', false)->count();

        $stats = [
            'total_incidents'    => $allIncidents->count(),
            'active_incidents'   => $allIncidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'investigating'      => $allIncidents->where('status', 'UNDER INVESTIGATION')->count(),
            'critical_incidents' => $allIncidents->where('severity', 'CRITICAL')->count(),
            'pending'            => $allIncidents->where('status', 'PENDING')->count(),
            'resolved'           => $allIncidents->where('status', 'RESOLVED')->count(),
            'my_reports'         => $incidents->count(),
            'notifications'      => $unreadNotifCount,
        ];

        return view('spectral.my_reports', compact('incidents', 'notifications', 'stats'));
    }
}
