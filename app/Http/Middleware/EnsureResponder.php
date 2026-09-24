<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureResponder
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('responder')->user();

        if (! $user) {
            return redirect()->route('responder.login');
        }

        if (! $user->isResponder()) {
            abort(403, 'Responder authorization is required.');
        }

        Auth::shouldUse('responder');

        return $next($request);
    }
}
