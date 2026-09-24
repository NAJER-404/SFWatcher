<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not authorized to access the Administrator Portal.',
                ], 403);
            }

            return redirect()->route('admin.login')->withErrors([
                'auth' => 'You are not authorized to access the Administrator Portal.',
            ]);
        }

        Auth::guard('admin')->setUser($user);

        return $next($request);
    }
}
