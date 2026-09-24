<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInvestigator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('investigator')->user();

        if (! $user) {
            return redirect()->route('investigator.login');
        }

        if ($user->role !== 'investigator') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not authorized to access the Investigator Portal.',
                ], 403);
            }

            return redirect()->route('investigator.login')->withErrors([
                'auth' => 'You are not authorized to access the Investigator Portal.',
            ]);
        }

        Auth::shouldUse('investigator');

        return $next($request);
    }
}
