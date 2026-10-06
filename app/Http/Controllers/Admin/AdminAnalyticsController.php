<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Investigation;
use App\Models\ResponderAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    /**
     * Dedicated System Analytics & Statistics
     */
    public function index(Request $request)
    {
        $timeframe = $request->input('timeframe', 'all');

        $incidentQuery = Incident::query();
        $userQuery = User::query();
        $assignQuery = ResponderAssignment::query();

        if ($timeframe === '7d') {
            $incidentQuery->where('created_at', '>=', now()->subDays(7));
            $userQuery->where('created_at', '>=', now()->subDays(7));
            $assignQuery->where('created_at', '>=', now()->subDays(7));
        } elseif ($timeframe === '30d') {
            $incidentQuery->where('created_at', '>=', now()->subDays(30));
            $userQuery->where('created_at', '>=', now()->subDays(30));
            $assignQuery->where('created_at', '>=', now()->subDays(30));
        } elseif ($timeframe === '90d') {
            $incidentQuery->where('created_at', '>=', now()->subDays(90));
            $userQuery->where('created_at', '>=', now()->subDays(90));
            $assignQuery->where('created_at', '>=', now()->subDays(90));
        } elseif ($timeframe === '1y') {
            $incidentQuery->where('created_at', '>=', now()->subYear());
            $userQuery->where('created_at', '>=', now()->subYear());
            $assignQuery->where('created_at', '>=', now()->subYear());
        }

        // 1. User Analytics
        $totalUsers = User::count();
        $userRoleBreakdown = [
            'Reporters'     => User::where('role', 'reporter')->count(),
            'Investigators' => User::where('role', 'investigator')->count(),
            'Responders'    => User::where('role', 'responder')->count(),
            'Admins'        => User::where('role', 'admin')->count(),
        ];

        // 2. Incident Analytics
        $totalIncidents = (clone $incidentQuery)->count();
        $severityCounts = [
            'LOW'      => (clone $incidentQuery)->where('severity', 'LOW')->count(),
            'MEDIUM'   => (clone $incidentQuery)->where('severity', 'MEDIUM')->count(),
            'HIGH'     => (clone $incidentQuery)->where('severity', 'HIGH')->count(),
            'CRITICAL' => (clone $incidentQuery)->where('severity', 'CRITICAL')->count(),
        ];

        $statusCounts = [
            'PENDING'             => (clone $incidentQuery)->where('status', 'PENDING')->count(),
            'UNDER INVESTIGATION' => (clone $incidentQuery)->where('status', 'UNDER INVESTIGATION')->count(),
            'VERIFIED'            => (clone $incidentQuery)->where('status', 'VERIFIED')->count(),
            'RESOLVED'            => (clone $incidentQuery)->where('status', 'RESOLVED')->count(),
        ];

        $resolvedTotal = (clone $incidentQuery)->where('status', 'RESOLVED')->count();
        $unresolvedTotal = $totalIncidents - $resolvedTotal;
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedTotal / $totalIncidents) * 100, 1) : 0;

        // Archived vs Visible resolved
        $archivedResolved = (clone $incidentQuery)->where('status', 'RESOLVED')->archivedFromMap()->count();
        $visibleResolved = (clone $incidentQuery)->where('status', 'RESOLVED')->activeOnMap()->count();

        // 3. Responder Analytics
        $totalResponders = User::where('role', 'responder')->count();
        $classBreakdown = [
            'Class D' => User::where('role', 'responder')->where('responder_class', 'D')->count(),
            'Class C' => User::where('role', 'responder')->where('responder_class', 'C')->count(),
            'Class B' => User::where('role', 'responder')->where('responder_class', 'B')->count(),
            'Class A' => User::where('role', 'responder')->where('responder_class', 'A')->count(),
        ];

        $availabilityBreakdown = [
            'Available'  => User::where('role', 'responder')->where('responder_status', 'AVAILABLE')->count(),
            'Responding' => User::where('role', 'responder')->where('responder_status', 'RESPONDING')->count(),
            'Inactive'   => User::where('role', 'responder')->where('responder_status', 'INACTIVE')->count(),
        ];

        $respondersList = User::where('role', 'responder')
            ->withCount(['responderAssignments as completed_count' => fn($q) => $q->where('status', 'COMPLETED')])
            ->orderByDesc('xp')
            ->get();

        $avgXp = $totalResponders > 0 ? round(User::where('role', 'responder')->avg('xp')) : 0;
        $totalXp = User::where('role', 'responder')->sum('xp');

        // 4. System Performance Calculations (from real timestamps)
        // Average investigation time: diff between incident reported and investigation completed
        $investigationsWithTimes = Investigation::whereNotNull('completed_at')
            ->orWhereNotNull('investigation_date')
            ->with('incident')
            ->get()
            ->filter(fn($inv) => $inv->incident && $inv->incident->created_at);

        $avgInvestigationMinutes = null;
        if ($investigationsWithTimes->isNotEmpty()) {
            $totalMins = 0;
            $count = 0;
            foreach ($investigationsWithTimes as $inv) {
                $comp = $inv->completed_at ?? $inv->investigation_date;
                $start = $inv->incident->incident_date ?? $inv->incident->created_at;
                if ($comp && $start && $comp >= $start) {
                    $totalMins += $start->diffInMinutes($comp);
                    $count++;
                }
            }
            if ($count > 0) {
                $avgInvestigationMinutes = round($totalMins / $count);
            }
        }

        // Average response time: diff between assignment and completion
        $assignmentsWithTimes = ResponderAssignment::whereNotNull('response_completed_at')
            ->whereNotNull('assigned_at')
            ->get();

        $avgResponseMinutes = null;
        if ($assignmentsWithTimes->isNotEmpty()) {
            $totalMins = 0;
            $count = 0;
            foreach ($assignmentsWithTimes as $as) {
                if ($as->response_completed_at >= $as->assigned_at) {
                    $totalMins += $as->assigned_at->diffInMinutes($as->response_completed_at);
                    $count++;
                }
            }
            if ($count > 0) {
                $avgResponseMinutes = round($totalMins / $count);
            }
        }

        $escalatedIncidents = (clone $incidentQuery)->where('severity', 'CRITICAL')->orWhere('status', 'ESCALATED')->count();

        // 5. Monthly incident trends (last 6 months from real DB records)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('M Y');
            $count = Incident::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $resolvedMonth = Incident::where('status', 'RESOLVED')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->count();
            $monthlyTrends[$key] = [
                'total'    => $count,
                'resolved' => $resolvedMonth,
            ];
        }

        return view('admin.analytics.index', compact(
            'timeframe',
            'totalUsers',
            'userRoleBreakdown',
            'totalIncidents',
            'severityCounts',
            'statusCounts',
            'resolvedTotal',
            'unresolvedTotal',
            'resolutionRate',
            'archivedResolved',
            'visibleResolved',
            'totalResponders',
            'classBreakdown',
            'availabilityBreakdown',
            'respondersList',
            'avgXp',
            'totalXp',
            'avgInvestigationMinutes',
            'avgResponseMinutes',
            'escalatedIncidents',
            'monthlyTrends'
        ));
    }
}
