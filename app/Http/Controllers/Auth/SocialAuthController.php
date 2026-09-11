<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the official Google OAuth consent/authentication page.
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        // Do not pretend Google authentication is working if OAuth credentials have not been configured
        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google OAuth is not configured yet. Please add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env file.',
            ]);
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Unable to initialize Google OAuth: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtain the user information from Google after authentication callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            $errorMsg = $request->get('error') === 'access_denied'
                ? 'Google authentication was cancelled by the user.'
                : 'Google authentication error: ' . $request->get('error');

            return redirect()->route('login')->withErrors(['google' => $errorMsg]);
        }

        if (!$request->has('code')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Missing Google authorization code in callback.',
            ]);
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();

            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();
            $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'SpectraWatch User';

            if (empty($email)) {
                return redirect()->route('login')->withErrors([
                    'google' => 'Google did not provide a verified email address for this account.',
                ]);
            }

            // 1. Try to find user by unique Google ID
            $user = User::where('google_id', $googleId)->first();

            // 2. If not found by google_id, match by email to prevent duplicate accounts
            if (!$user) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Link existing email account to Google ID
                    $user->google_id = $googleId;
                    if (empty($user->email_verified_at)) {
                        $user->email_verified_at = now();
                    }
                    $user->save();
                }
            }

            // 3. If user does not exist, create a new SpectraWatch account
            if (!$user) {
                $user = User::create([
                    'name'              => $name,
                    'email'             => $email,
                    'google_id'         => $googleId,
                    'role'              => 'reporter', // Strict default: reporter only
                    'password'          => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }

            // 4. Authenticate the user via Laravel's session
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('spectral.dashboard'));

        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google authentication failed: ' . $e->getMessage(),
            ]);
        }
    }
}
