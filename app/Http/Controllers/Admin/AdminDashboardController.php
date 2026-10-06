<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Incident;
use App\Models\Investigation;
use App\Models\PromotionHistory;
use App\Models\ResponderAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    /**
     * Admin Overview Dashboard
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        // 1. User Statistics (all from database)
        $userStats = [
            'total_users'         => User::count(),
            'total_reporters'     => User::where('role', 'reporter')->count(),
            'total_investigators' => User::where('role', 'investigator')->count(),
            'total_responders'    => User::where('role', 'responder')->count(),
            'total_admins'        => User::where('role', 'admin')->count(),
        ];

        // 2. Incident Statistics (all from database)
        $incidentStats = [
            'total_incidents'     => Incident::count(),
            'pending_incidents'   => Incident::where('status', 'PENDING')->count(),
            'under_investigation' => Incident::where('status', 'UNDER INVESTIGATION')->count(),
            'confirmed_incidents' => Incident::where('status', 'VERIFIED')->count(),
            'under_response'      => Incident::where(function ($q) {
                $q->where('response_status', 'ACTIVE')
                  ->orWhereHas('responderAssignments', fn($sub) => $sub->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'IN_PROGRESS']));
            })->count(),
            'escalated_incidents' => Incident::where('severity', 'CRITICAL')->orWhere('status', 'ESCALATED')->count(),
            'resolved_incidents'  => Incident::where('status', 'RESOLVED')->count(),
            'archived_incidents'  => Incident::archivedFromMap()->count(),
        ];

        // 3. Responder Statistics (all from database)
        $responderStats = [
            'class_d'   => User::where('role', 'responder')->where('responder_class', 'D')->count(),
            'class_c'   => User::where('role', 'responder')->where('responder_class', 'C')->count(),
            'class_b'   => User::where('role', 'responder')->where('responder_class', 'B')->count(),
            'class_a'   => User::where('role', 'responder')->where('responder_class', 'A')->count(),
            'available' => User::where('role', 'responder')->where('responder_status', 'AVAILABLE')->count(),
        ];

        // 4. Recent System Activity (real events from database)
        $recentActivity = collect();

        // A. Admin Activity Logs (archive/restore, promotions, etc.)
        AdminActivityLog::with('user')->latest()->take(10)->get()->each(function ($log) use (&$recentActivity) {
            $recentActivity->push([
                'type'        => 'ADMIN ACTION',
                'title'       => $log->action,
                'description' => $log->description,
                'actor'       => $log->user?->name ?? 'System Admin',
                'timestamp'   => $log->created_at,
            ]);
        });

        // B. New User Registrations
        User::latest()->take(5)->get()->each(function ($user) use (&$recentActivity) {
            $recentActivity->push([
                'type'        => 'USER REGISTRATION',
                'title'       => 'New Account Registered',
                'description' => "{$user->name} ({$user->email}) registered as " . strtoupper($user->role),
                'actor'       => $user->name,
                'timestamp'   => $user->created_at,
            ]);
        });

        // C. New Incident Reports
        Incident::latest()->take(5)->get()->each(function ($inc) use (&$recentActivity) {
            $recentActivity->push([
                'type'        => 'INCIDENT REPORT',
                'title'       => "Incident Logged: {$inc->incident_code}",
                'description' => "{$inc->title} ({$inc->incident_type}) — Severity: {$inc->severity}",
                'actor'       => $inc->reporter?->name ?? 'Field Scout',
                'timestamp'   => $inc->created_at,
            ]);
        });

        // D. Investigation Notes / Confirmations
        Investigation::with(['incident', 'investigator'])->latest()->take(5)->get()->each(function ($inv) use (&$recentActivity) {
            $code = $inv->incident?->incident_code ?? 'Incident';
            $recentActivity->push([
                'type'        => 'INVESTIGATION',
                'title'       => "Investigation Update for {$code}",
                'description' => ($inv->notes ?: 'Assessment logged') . " [Result: {$inv->result}]",
                'actor'       => $inv->investigator?->name ?? 'Investigator',
                'timestamp'   => $inv->investigation_date ?? $inv->created_at,
            ]);
        });

        // E. Responder Assignments
        ResponderAssignment::with(['incident', 'investigator', 'responder'])->latest()->take(5)->get()->each(function ($assign) use (&$recentActivity) {
            $code = $assign->incident?->incident_code ?? 'Incident';
            $resp = $assign->responder?->name ?? 'Responder';
            $recentActivity->push([
                'type'        => 'DISPATCH',
                'title'       => "Responder Assigned to {$code}",
                'description' => "{$resp} deployed for anomaly containment [Status: {$assign->status}]",
                'actor'       => $assign->investigator?->name ?? 'Investigator',
                'timestamp'   => $assign->assigned_at ?? $assign->created_at,
            ]);
        });

        // F. Responder Promotions
        PromotionHistory::with(['user', 'promoter'])->latest()->take(5)->get()->each(function ($promo) use (&$recentActivity) {
            $name = $promo->user?->name ?? 'Responder';
            $recentActivity->push([
                'type'        => 'PROMOTION',
                'title'       => "Class Promotion: {$name}",
                'description' => "Promoted from Class {$promo->from_class} to Class {$promo->to_class} at {$promo->xp_at_promotion} XP",
                'actor'       => $promo->promoter?->name ?? 'Administrator',
                'timestamp'   => $promo->created_at,
            ]);
        });

        // Sort descending by timestamp and take top 15
        $recentActivity = $recentActivity->filter(fn($item) => !empty($item['timestamp']))
            ->sortByDesc('timestamp')
            ->values()
            ->take(15);

        return view('admin.dashboard', compact(
            'admin',
            'userStats',
            'incidentStats',
            'responderStats',
            'recentActivity'
        ));
    }

    /**
     * Admin Profile view
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();
        $recentLogs = AdminActivityLog::where('user_id', $admin->id)->latest()->take(10)->get();

        return view('admin.profile', compact('admin', 'recentLogs'));
    }

    /**
     * Update Admin Profile
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $admin->name  = $validated['name'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        AdminActivityLog::create([
            'user_id'      => $admin->id,
            'action'       => 'ACCOUNT_UPDATED',
            'subject_type' => 'User',
            'subject_id'   => $admin->id,
            'description'  => "Administrator profile updated for {$admin->name}.",
        ]);

        return back()->with('success', 'Administrator profile updated successfully.');
    }

    /**
     * Account Settings view
     */
    public function settings()
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();
        return view('admin.settings', compact('admin'));
    }

    /**
     * Update System / Account Settings
     */
    public function updateSettings(Request $request)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        AdminActivityLog::create([
            'user_id'      => $admin->id,
            'action'       => 'SYSTEM_SETTINGS_UPDATED',
            'subject_type' => 'System',
            'subject_id'   => $admin->id,
            'description'  => 'System administrative preferences refreshed.',
        ]);

        return back()->with('success', 'System settings saved.');
    }
}
