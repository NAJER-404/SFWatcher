<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Resource;
use App\Models\WardStation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['barangay', 'reporter', 'evidence', 'investigations'])
            ->orderByDesc('created_at')
            ->get();

        $wardStations = WardStation::with('barangay')->get();
        $resources = Resource::with('barangay')->get();
        $barangays = Barangay::orderBy('name')->get();
        $equipment = Equipment::all();

        $stats = [
            'total_incidents'     => $incidents->count(),
            'active_incidents'    => $incidents->whereIn('status', ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED', 'ESCALATED'])->count(),
            'investigating'       => $incidents->where('status', 'UNDER INVESTIGATION')->count(),
            'critical_incidents'  => $incidents->where('severity', 'CRITICAL')->count(),
            'ward_stations'       => $wardStations->count(),
            'resources'           => $resources->count(),
        ];

        return view('spectral.dashboard', compact('incidents', 'wardStations', 'resources', 'barangays', 'equipment', 'stats'));
    }
}
