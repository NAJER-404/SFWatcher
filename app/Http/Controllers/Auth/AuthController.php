<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('spectral.dashboard');
        }
        return view('auth.login');
    }

    public function showAdminLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('spectral.dashboard');
            }
            return redirect()->route('spectral.dashboard');
        }
        return view('auth.admin_login');
    }

    public function showInvestigatorLoginForm()
    {
        if (Auth::guard('investigator')->check() && Auth::guard('investigator')->user()->isInvestigator()) {
            return redirect()->route('investigator.dashboard');
        }
        return view('auth.investigator_login');
    }

    public function investigatorLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('investigator')->attempt($credentials, $remember)) {
            $user = Auth::guard('investigator')->user();

            if (! $user->isInvestigator()) {
                Auth::guard('investigator')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'You are not authorized to access the Investigator Portal.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('investigator.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our defense network records.',
        ])->onlyInput('email');
    }

    public function investigatorLogout(Request $request)
    {
        Auth::guard('investigator')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('investigator.login');
    }

    public function showResponderLoginForm()
    {
        if (Auth::guard('responder')->check() && Auth::guard('responder')->user()->isResponder()) return redirect()->route('responder.dashboard');
        return view('auth.responder_login');
    }

    public function responderLogin(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (Auth::guard('responder')->attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::guard('responder')->user()->isResponder()) {
                Auth::guard('responder')->logout();
                return back()->withErrors(['email' => 'You are not authorized to access the Responder Portal.'])->onlyInput('email');
            }
            $request->session()->regenerate();
            return redirect()->intended(route('responder.dashboard'));
        }
        return back()->withErrors(['email' => 'The provided credentials do not match responder records.'])->onlyInput('email');
    }

    public function responderLogout(Request $request)
    {
        Auth::guard('responder')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('responder.login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            if (! Auth::guard('admin')->user()->isAdmin()) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'You are not authorized to access the Administrator Portal.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('spectral.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our defense network records.',
        ])->onlyInput('email');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $user = Auth::guard('web')->user();

            // Only reporters use this portal. Block investigators/responders/admins.
            if ($user->role === 'investigator') {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'Investigator accounts must use the Investigator Portal at /investigator/login.',
                ])->onlyInput('email');
            }
            if ($user->role === 'responder') {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'Responder accounts must use the Responder Portal at /responder/login.',
                ])->onlyInput('email');
            }
            if ($user->role === 'admin') {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'Administrator accounts must use the Admin Portal at /admin/login.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('spectral.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our defense network records.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('spectral.dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'terms'    => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms of Use and Privacy Policy.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'reporter', // Never allow public signup to choose investigator
        ]);

        return redirect()->route('login')->with('account_created', 'Account has been created.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
