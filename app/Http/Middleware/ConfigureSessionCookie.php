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
     * Ensure the Investigator portal and standard user sessions use separate cookies
     * so that logging in/out of one does not collide with or terminate the other.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('investigator*')) {
            config(['session.cookie' => 'spectrawatch_investigator_session']);
        } else {
            config(['session.cookie' => 'spectrawatch_session']);
        }

        return $next($request);
    }
}
