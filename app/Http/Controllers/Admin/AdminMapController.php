<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Resource;
use App\Models\User;
use App\Models\WardStation;

class AdminMapController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'responderAssignments.responder'])
            ->orderByDesc('created_at')
            ->get();

        $safeZones = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('ward_stations', 'is_safe_zone')) {
                $safeZones = WardStation::with('barangay')
                    ->where('status', 'active')
                    ->where('is_safe_zone', true)
                    ->get()
                    ->map(function ($ws) {
                        $radius = $ws->safe_zone_radius_meters ?? $ws->radius_meters ?? 450;

                        return [
                            'id'          => $ws->id,
                            'code'        => $ws->code,
                            'name'        => $ws->name,
                            'description' => $ws->description,
                            'lat'         => (float) $ws->latitude,
                            'lng'         => (float) $ws->longitude,
                            'radius'      => (float) $radius,
                            'status'      => 'active',
                            'barangay'    => $ws->barangay?->name ?? 'San Francisco',
                            'address'     => 'Brgy. ' . ($ws->barangay?->name ?? 'San Francisco') . ', San Francisco, Agusan del Sur',

                            // 🆕 Safe zone details
                            'protection_expires_at' => $ws->ward_protection_expires_at
                                ? $ws->ward_protection_expires_at->toIso8601String()
                                : null,
                            'protection_active'     => $ws->isProtectionActive(),
                            'protection_time'       => $ws->protectionTimeRemaining(),
                            'relief_food'           => $ws->relief_food ?? 'available',
                            'relief_water'          => $ws->relief_water ?? 'available',
                            'relief_first_aid'      => $ws->relief_first_aid ?? 'available',
                            'assistance_services'   => $ws->assistance_services ?? [],
                        ];
                    })
                    ->all();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AdminMapController safeZones query failed: ' . $e->getMessage());
        }

        if (empty($safeZones)) {
            $safeZones = [
                [
                    'id'                    => null,
                    'code'                  => 'SZ-001',
                    'name'                  => 'San Francisco Municipal Gymnasium Sanctuary',
                    'description'           => 'Primary designated municipal emergency sanctuary and safe zone.',
                    'lat'                   => 8.5098,
                    'lng'                   => 125.9780,
                    'radius'                => 450.0,
                    'status'                => 'active',
                    'barangay'              => 'Barangay 1',
                    'address'               => 'Brgy. Barangay 1, San Francisco, Agusan del Sur',
                    'protection_expires_at' => null,
                    'protection_active'     => true,
                    'protection_time'       => null,
                    'relief_food'           => 'available',
                    'relief_water'          => 'available',
                    'relief_first_aid'      => 'available',
                    'assistance_services'   => ['Emergency Shelter', 'First Aid', 'Relief Goods'],
                ],
                [
                    'id'                    => null,
                    'code'                  => 'SZ-002',
                    'name'                  => 'Hubang Transport Safe Haven',
                    'description'           => 'Perimeter-secured transport and evacuation depot safe zone.',
                    'lat'                   => 8.5310,
                    'lng'                   => 125.9730,
                    'radius'                => 550.0,
                    'status'                => 'active',
                    'barangay'              => 'Hubang',
                    'address'               => 'Brgy. Hubang, San Francisco, Agusan del Sur',
                    'protection_expires_at' => null,
                    'protection_active'     => true,
                    'protection_time'       => null,
                    'relief_food'           => 'available',
                    'relief_water'          => 'available',
                    'relief_first_aid'      => 'available',
                    'assistance_services'   => ['Evacuation Transport', 'Supply Depot', 'Medical Station'],
                ],
            ];
        }

        $wardStations = WardStation::with('barangay')->get();
        $resources    = Resource::with('barangay')->get();
        $barangays    = Barangay::orderBy('name')->get();

        $stats = [
            'total_incidents'      => $incidents->count(),
            'active_incidents'     => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'])
                                                ->whereNull('archived_from_map_at')
                                                ->count(),
            'critical'             => $incidents->where('severity', 'CRITICAL')->count(),
            'high'                 => $incidents->where('severity', 'HIGH')->count(),
            'resolved'             => $incidents->where('status', 'RESOLVED')
                                                ->whereNull('archived_from_map_at')
                                                ->count(),
            'archived'             => $incidents->whereNotNull('archived_from_map_at')->count(),
            'safe_zones'           => count($safeZones),
            'responders_available' => User::where('role', 'responder')->where('responder_status', 'AVAILABLE')->count(),
        ];

        return view('admin.map', compact('incidents', 'safeZones', 'wardStations', 'resources', 'barangays', 'stats'));
    }
}
