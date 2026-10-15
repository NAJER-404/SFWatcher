<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Resource;
use App\Models\WardStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        return view('spectral.dashboard', $this->getDashboardPayload());
    }

    public function map()
    {
        return view('spectral.map', $this->getDashboardPayload());
    }

    private function getDashboardPayload(): array
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations', 'responderAssignments'])
            ->activeOnMap()
            ->orderByDesc('created_at')
            ->get();

        $safeWardStations = [
            (object)[
                'name'     => 'San Francisco Municipal Gymnasium Sanctuary',
                'lat'      => 8.5098,
                'lng'      => 125.9780,
                'radius'   => 450,
                'status'   => 'active',
                'barangay' => 'Barangay 1',
            ],
            (object)[
                'name'     => 'Hubang Transport Safe Haven',
                'lat'      => 8.5310,
                'lng'      => 125.9730,
                'radius'   => 550,
                'status'   => 'active',
                'barangay' => 'Hubang',
            ],
        ];

        $wardStations = WardStation::with('barangay')->get();
        $resources    = Resource::with('barangay')->get();
        $barangays    = Barangay::orderBy('name')->get();
        $equipment    = Equipment::all();

        $userId = Auth::id();

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
            });

        $rejectedNotifs = collect(Cache::get("user_notifications_{$userId}", []))
            ->map(function ($notif) use ($readNotifs) {
                $notif['is_read'] = in_array((string)$notif['incident_id'], array_map('strval', $readNotifs));
                return $notif;
            });

        // ✅ Full merged list (before limiting for display)
        $allNotifications = $notifications->concat($rejectedNotifs)
            ->sortByDesc('updated_at')
            ->values();

        // ✅ Count unread from the FULL list
        $unreadNotifCount = $allNotifications->where('is_read', false)->count();

        // ✅ But only DISPLAY the top 8 in the dropdown
        $notifications = $allNotifications->take(8);

        $stats = [
            'total_incidents'    => $incidents->count(),
            'active_incidents'   => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'investigating'      => $incidents->where('status', 'UNDER INVESTIGATION')->count(),
            'critical_incidents' => $incidents->where('severity', 'CRITICAL')->count(),
            'ward_stations'      => count($safeWardStations),
            'resources'          => $resources->count(),
            'my_reports'         => $myIncidents->count(),
            'pending'            => $incidents->where('status', 'PENDING')->count(),
            'resolved'           => $incidents->where('status', 'RESOLVED')->count(),
            'notifications'      => $unreadNotifCount,
        ];

        return compact(
            'incidents', 'wardStations', 'safeWardStations', 'resources', 'barangays', 'equipment', 'stats', 'notifications', 'unreadNotifCount'
        );
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
            ->values();

        // Merge rejected notifications from cache
        $rejectedCached = Cache::get("user_notifications_{$userId}", []);
        if (!empty($rejectedCached)) {
            $rejectedMapped = collect($rejectedCached)->map(function ($n) use ($readNotifs) {
                return [
                    'incident_code' => $n['incident_code'] ?? 'N/A',
                    'title'         => $n['title']         ?? 'Incident',
                    'status'        => 'REJECTED',
                    'severity'      => $n['severity']      ?? '—',
                    'investigator'  => $n['investigator']  ?? 'System',
                    'notes'         => $n['notes']         ?? null,
                    'updated_at'    => $n['updated_at']    ?? now(),
                    'incident_id'   => $n['incident_id']   ?? null,
                    'is_read'       => in_array((string) ($n['incident_id'] ?? ''), array_map('strval', $readNotifs)),
                ];
            });
            $notifications = $notifications->concat($rejectedMapped);
        }

        // ✅ Count unread from the FULL list (before limiting)
        $unreadNotifCount = $notifications->where('is_read', false)->count();

        // ✅ Then limit for dropdown display
        $notifications = $notifications->sortByDesc('updated_at')->values()->take(8);

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

        return view('spectral.my_reports', compact('incidents', 'notifications', 'stats', 'unreadNotifCount'));
    }

    public function profile()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $incidents = Incident::with(['barangay', 'evidence', 'investigations.investigator'])
            ->where('reported_by', $userId)
            ->latest()
            ->get();

        $allIncidents = Incident::all();
        $readNotifs   = session('read_notifications', []);

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
            ->values();

        // Merge rejected notifications from cache
        $rejectedCached = Cache::get("user_notifications_{$userId}", []);
        if (!empty($rejectedCached)) {
            $rejectedMapped = collect($rejectedCached)->map(function ($n) use ($readNotifs) {
                return [
                    'incident_code' => $n['incident_code'] ?? 'N/A',
                    'title'         => $n['title']         ?? 'Incident',
                    'status'        => 'REJECTED',
                    'severity'      => $n['severity']      ?? '—',
                    'investigator'  => $n['investigator']  ?? 'System',
                    'notes'         => $n['notes']         ?? null,
                    'updated_at'    => $n['updated_at']    ?? now(),
                    'incident_id'   => $n['incident_id']   ?? null,
                    'is_read'       => in_array((string) ($n['incident_id'] ?? ''), array_map('strval', $readNotifs)),
                ];
            });
            $notifications = $notifications->concat($rejectedMapped);
        }

        // ✅ Count unread from the FULL list (before limiting)
        $unreadNotifCount = $notifications->where('is_read', false)->count();

        // ✅ Then limit for dropdown display
        $notifications = $notifications->sortByDesc('updated_at')->values()->take(8);

        $stats = [
            'total_incidents'    => $allIncidents->count(),
            'active_incidents'   => $allIncidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])->count(),
            'pending'            => $incidents->where('status', 'PENDING')->count(),
            'resolved'           => $incidents->where('status', 'RESOLVED')->count(),
            'my_reports'         => $incidents->count(),
            'notifications'      => $unreadNotifCount,
        ];

        return view('spectral.reporter_profile', compact('incidents', 'stats', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        /** @var \App\Models\User $user */
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Full notification center for reporters.
     */
    public function notifications(Request $request)
    {
        $userId = Auth::id();
        $readNotifs = session('read_notifications', []);

        $incidents = Incident::with(['barangay', 'investigations.investigator'])
            ->where(function ($query) use ($userId) {
                $query->where('reported_by', $userId)
                      ->orWhereHas('evidence', function ($q) use ($userId) {
                          $q->where('uploaded_by', $userId);
                      });
            })
            ->orderByDesc('updated_at')
            ->get();

        $notifications = $incidents
            ->filter(fn($inc) => $inc->investigations->isNotEmpty())
            ->map(function ($inc) use ($readNotifs) {
                $latest = $inc->investigations->sortByDesc('investigation_date')->first();
                return (object) [
                    'id'            => 'incident-' . $inc->id,
                    'type'          => strtolower($inc->status) === 'under investigation' ? 'investigation' : strtolower($inc->status),
                    'title'         => 'Update on ' . $inc->incident_code,
                    'message'       => $latest?->notes ?? 'Status changed to ' . $inc->status,
                    'incident_code' => $inc->incident_code,
                    'incident_id'   => $inc->id,
                    'link'          => route('spectral.incidents.show', $inc->id),
                    'created_at'    => $latest?->investigation_date ?? $inc->updated_at,
                    'read'          => in_array($inc->id, $readNotifs),
                ];
            });

        $rejectedCached = collect(Cache::get("user_notifications_{$userId}", []))
            ->map(function ($n) use ($readNotifs) {
                return (object) [
                    'id'            => 'rejected-' . ($n['incident_id'] ?? uniqid()),
                    'type'          => 'rejected',
                    'title'         => 'Report Rejected',
                    'message'       => $n['notes'] ?? 'Your report was rejected by an investigator.',
                    'incident_code' => $n['incident_code'] ?? 'N/A',
                    'incident_id'   => $n['incident_id'] ?? null,
                    'link'          => isset($n['incident_id']) ? route('spectral.incidents.show', $n['incident_id']) : null,
                    'created_at'    => \Carbon\Carbon::parse($n['updated_at'] ?? now()),
                    'read'          => in_array((string)($n['incident_id'] ?? ''), array_map('strval', $readNotifs)),
                ];
            });

        $all = $notifications->concat($rejectedCached)->sortByDesc('created_at')->values();

        $totalCount  = $all->count();
        $unreadCount = $all->where('read', false)->count();

        $filter = $request->input('filter');
        if ($filter === 'unread') {
            $all = $all->where('read', false)->values();
        } elseif ($filter === 'read') {
            $all = $all->where('read', true)->values();
        }

        $perPage = 20;
        $page    = (int) $request->input('page', 1);
        $sliced  = $all->slice(($page - 1) * $perPage, $perPage)->values();

        $notifications = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('spectral.notifications', compact('notifications', 'totalCount', 'unreadCount'));
    }
}