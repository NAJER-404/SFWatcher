<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * Ensure each portal uses a separate session cookie so that logging in/out
     * of one guard does not collide with or terminate another guard's session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('investigator*')) {
            config(['session.cookie' => 'spectrawatch_investigator_session']);
        } elseif ($request->is('admin*')) {
            config(['session.cookie' => 'spectrawatch_admin_session']);
        } elseif ($request->is('responder*')) {
            config(['session.cookie' => 'spectrawatch_responder_session']);
        } else {
            config(['session.cookie' => 'spectrawatch_session']);
        }

        return $next($request);
    }
}

