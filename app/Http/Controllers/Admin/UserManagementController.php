<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index() { return view('admin.users.index', ['users' => User::orderBy('name')->paginate(25)]); }
    public function updateRole(Request $request, User $user) { $user->update($request->validate(['role' => 'required|in:reporter,investigator,responder,admin'])); return back()->with('success', 'Role updated. Public registration remains Reporter-only.'); }
    public function promote(User $user) {
        abort_unless($user->isResponder() && $user->isPromotionEligible(), 422, 'This responder is not eligible for promotion.');
        $next = match ($user->responder_class) { 'D' => 'C', 'C' => 'B', 'B' => 'A' };
        PromotionHistory::create(['user_id' => $user->id, 'promoted_by' => Auth::guard('admin')->id() ?? Auth::id(), 'from_class' => $user->responder_class, 'to_class' => $next, 'xp_at_promotion' => $user->xp]);
        $user->update(['responder_class' => $next]);
        return back()->with('success', "{$user->name} promoted to Class {$next}.");
    }
}
