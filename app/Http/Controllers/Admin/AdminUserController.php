<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\PromotionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /**
     * All Users list with filters, search, and pagination.
     */
    public function index(Request $request)
    {
        return $this->renderUserList($request, null, 'All Users');
    }

    /**
     * Investigators view
     */
    public function investigators(Request $request)
    {
        return $this->renderUserList($request, 'investigator', 'Investigators');
    }

    /**
     * Responders view
     */
    public function responders(Request $request)
    {
        return $this->renderUserList($request, 'responder', 'Responders');
    }

    /**
     * Reporters view
     */
    public function reporters(Request $request)
    {
        return $this->renderUserList($request, 'reporter', 'Reporters');
    }

    /**
     * Core helper to build filtered user list
     */
    private function renderUserList(Request $request, ?string $lockedRole, string $sectionTitle)
    {
        $query = User::query();

        // Locked role tab or dropdown filter
        $role = $lockedRole ?? $request->input('role');
        if ($role && in_array($role, ['reporter', 'investigator', 'responder', 'admin'])) {
            $query->where('role', $role);
        }

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by responder class (if applicable)
        if ($class = $request->input('responder_class')) {
            $query->where('responder_class', strtoupper($class));
        }

        // Filter by responder availability status
        if ($status = $request->input('status')) {
            if ($status === 'AVAILABLE') {
                $query->where('responder_status', 'AVAILABLE');
            } elseif ($status === 'RESPONDING') {
                $query->where('responder_status', 'RESPONDING');
            } elseif ($status === 'INACTIVE') {
                $query->where('responder_status', 'INACTIVE');
            }
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        $roleCounts = [
            'all'           => User::count(),
            'reporter'      => User::where('role', 'reporter')->count(),
            'investigator'  => User::where('role', 'investigator')->count(),
            'responder'     => User::where('role', 'responder')->count(),
            'admin'         => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'sectionTitle', 'lockedRole', 'roleCounts'));
    }

    /**
     * Inspect individual user profile
     */
    public function show(User $user)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();

        // Load role-specific relations
        $user->load([
            'incidents.barangay',
            'investigations.incident',
            'responderAssignments.incident',
            'promotionHistory.promoter',
        ]);

        $classesConfig = config('spectral_response.classes');

        return view('admin.users.show', compact('user', 'admin', 'classesConfig'));
    }

    /**
     * Edit user form
     */
    public function edit(User $user)
    {
        $admin = Auth::guard('admin')->user() ?? Auth::user();
        return view('admin.users.edit', compact('user', 'admin'));
    }

    /**
     * Update user details
     */
    public function update(Request $request, User $user)
    {
        $currentAdmin = Auth::guard('admin')->user() ?? Auth::user();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'             => 'required|in:reporter,investigator,responder,admin',
            'responder_class'  => 'nullable|in:D,C,B,A',
            'responder_status' => 'nullable|in:AVAILABLE,RESPONDING,INACTIVE',
            'xp'               => 'nullable|integer|min:0',
        ]);

        // Security safeguard: Protect admin from demoting themselves or the primary system admin
        if ($user->id === $currentAdmin->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Security Error: You cannot remove administrator privileges from your own active account.']);
        }

        if ($user->email === 'admin@ectonet.gov' && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Security Error: The primary administration account cannot be demoted.']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($user->role === 'responder') {
            $user->responder_class = $validated['responder_class'] ?? $user->responder_class ?? 'D';
            $user->responder_status = $validated['responder_status'] ?? $user->responder_status ?? 'AVAILABLE';
            if (isset($validated['xp'])) {
                $user->xp = (int) $validated['xp'];
            }
        }

        $user->save();

        AdminActivityLog::create([
            'user_id'      => $currentAdmin->id,
            'action'       => 'USER_UPDATED',
            'subject_type' => 'User',
            'subject_id'   => $user->id,
            'description'  => "Updated profile details for {$user->name} ({$user->email}).",
            'metadata'     => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} has been updated successfully.");
    }

    /**
     * Delete user with security safeguards
     */
    public function destroy(User $user)
    {
        $currentAdmin = Auth::guard('admin')->user() ?? Auth::user();

        if ($user->id === $currentAdmin->id) {
            return back()->withErrors(['user' => 'Security Error: You cannot delete your own active administrator account.']);
        }

        if ($user->email === 'admin@ectonet.gov') {
            return back()->withErrors(['user' => 'Security Error: The primary Commander administration account cannot be deleted.']);
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;

        DB::transaction(function () use ($user, $currentAdmin, $userName, $userEmail, $userRole) {
            $user->responderAssignments()->delete();
            $user->promotionHistory()->delete();
            $user->investigations()->delete();
            $user->incidents()->update(['reported_by' => null]);

            $user->delete();

            AdminActivityLog::create([
                'user_id'      => $currentAdmin->id,
                'action'       => 'USER_DELETED',
                'subject_type' => 'User',
                'subject_id'   => null,
                'description'  => "Deleted user {$userName} ({$userEmail}, Role: {$userRole}).",
                'metadata'     => ['name' => $userName, 'email' => $userEmail, 'role' => $userRole],
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', "User {$userName} was successfully deleted.");
    }

    /**
     * Update User Role with security safeguards
     */
    public function updateRole(Request $request, User $user)
    {
        $currentAdmin = Auth::guard('admin')->user() ?? Auth::user();

        $validated = $request->validate([
            'role' => 'required|in:reporter,investigator,responder,admin',
        ]);

        // Security safeguard: Protect admin from demoting themselves or the primary system admin
        if ($user->id === $currentAdmin->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Security Error: You cannot remove administrator privileges from your own active account.']);
        }

        if ($user->email === 'admin@ectonet.gov' && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Security Error: The primary Commander administration account cannot be demoted.']);
        }

        $oldRole = $user->role;
        $user->role = $validated['role'];

        // Default responder fields if moving to responder
        if ($validated['role'] === 'responder' && empty($user->responder_class)) {
            $user->responder_class = 'D';
            $user->responder_status = 'AVAILABLE';
        }

        $user->save();

        AdminActivityLog::create([
            'user_id'      => $currentAdmin->id,
            'action'       => 'USER_ROLE_CHANGED',
            'subject_type' => 'User',
            'subject_id'   => $user->id,
            'description'  => "Changed role for {$user->name} ({$user->email}) from {$oldRole} to {$user->role}.",
            'metadata'     => ['old_role' => $oldRole, 'new_role' => $user->role],
        ]);

        return back()->with('success', "Role for {$user->name} updated to " . strtoupper($user->role) . ".");
    }

    /**
     * Promote Responder to Next Class
     */
    public function promote(Request $request, User $user)
    {
        $currentAdmin = Auth::guard('admin')->user() ?? Auth::user();

        if (! $user->isResponder()) {
            return back()->withErrors(['promotion' => 'Only accounts with the Responder role can be promoted.']);
        }

        $fromClass = strtoupper($user->responder_class ?? 'D');
        $nextClass = match ($fromClass) {
            'D' => 'C',
            'C' => 'B',
            'B' => 'A',
            default => null,
        };

        if (! $nextClass) {
            return back()->withErrors(['promotion' => "Responder {$user->name} is already at the maximum Class A tier."]);
        }

        $requiredXp = config("spectral_response.classes.{$fromClass}.promotion_xp");
        if ($requiredXp !== null && $user->xp < $requiredXp) {
            // Check if admin is overriding or normal promotion
            if (! $request->boolean('force')) {
                return back()->withErrors(['promotion' => "Responder has {$user->xp} XP but requires {$requiredXp} XP to qualify for Class {$nextClass}."]);
            }
        }

        DB::transaction(function () use ($user, $fromClass, $nextClass, $currentAdmin) {
            PromotionHistory::create([
                'user_id'          => $user->id,
                'promoted_by'      => $currentAdmin->id,
                'from_class'       => $fromClass,
                'to_class'         => $nextClass,
                'xp_at_promotion'  => $user->xp,
            ]);

            $user->responder_class = $nextClass;
            $user->save();

            $newMaxHp = config("spectral_response.classes.{$nextClass}.responder_hp", 100);

            AdminActivityLog::create([
                'user_id'      => $currentAdmin->id,
                'action'       => 'RESPONDER_PROMOTED',
                'subject_type' => 'User',
                'subject_id'   => $user->id,
                'description'  => "Approved promotion for {$user->name} from Class {$fromClass} to Class {$nextClass} (New Max HP: {$newMaxHp}). Preserved {$user->xp} XP.",
                'metadata'     => [
                    'from_class' => $fromClass,
                    'to_class'   => $nextClass,
                    'xp'         => $user->xp,
                    'max_hp'     => $newMaxHp,
                ],
            ]);
        });

        return back()->with('success', "Promotion approved. {$user->name} is now Class {$nextClass}. Maximum HP updated to " . config("spectral_response.classes.{$nextClass}.responder_hp") . " HP.");
    }

    /**
     * Manual Responder Class update by Admin
     */
    public function updateClass(Request $request, User $user)
    {
        $currentAdmin = Auth::guard('admin')->user() ?? Auth::user();

        if (! $user->isResponder()) {
            return back()->withErrors(['class' => 'Only responders can have their tactical class adjusted.']);
        }

        $validated = $request->validate([
            'responder_class' => 'required|in:D,C,B,A',
        ]);

        $oldClass = $user->responder_class;
        $newClass = $validated['responder_class'];

        if ($oldClass === $newClass) {
            return back()->with('info', 'Tactical class remains unchanged.');
        }

        DB::transaction(function () use ($user, $oldClass, $newClass, $currentAdmin) {
            PromotionHistory::create([
                'user_id'          => $user->id,
                'promoted_by'      => $currentAdmin->id,
                'from_class'       => $oldClass,
                'to_class'         => $newClass,
                'xp_at_promotion'  => $user->xp,
            ]);

            $user->responder_class = $newClass;
            $user->save();

            $newMaxHp = config("spectral_response.classes.{$newClass}.responder_hp", 100);

            AdminActivityLog::create([
                'user_id'      => $currentAdmin->id,
                'action'       => 'RESPONDER_CLASS_CHANGED',
                'subject_type' => 'User',
                'subject_id'   => $user->id,
                'description'  => "Direct tactical class adjustment for {$user->name}: Class {$oldClass} -> Class {$newClass} (Max HP: {$newMaxHp}).",
                'metadata'     => ['from_class' => $oldClass, 'to_class' => $newClass],
            ]);
        });

        return back()->with('success', "Tactical class for {$user->name} set to Class {$newClass}. Maximum HP updated to " . config("spectral_response.classes.{$newClass}.responder_hp") . " HP.");
    }
}
