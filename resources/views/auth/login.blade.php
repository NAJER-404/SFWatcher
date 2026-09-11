<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectra — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: #0B0F14; font-family: 'Plus Jakarta Sans', sans-serif; color: #E2E8F0; }
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .auth-card {
            width: 100%;
            max-width: 360px;
            background: #151B23;
            border: 1px solid #2A3440;
            border-radius: 14px;
            padding: 32px 28px 28px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
        }
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0px;
            margin-bottom: 18px;
        }
        .auth-brand img { width: 78px; height: 78px; object-fit: contain; }
        .auth-brand-name {
            font-size: 28px; font-weight: 800; letter-spacing: 0.03em;
            margin-top: 5px;
            background: linear-gradient(135deg, #3B82F6 0%, #06B6D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .auth-title { font-size: 14px; font-weight: 500; color: #6B7280; text-align: center; margin-top: 5px; margin-bottom: 24px; }
        .auth-divider { height: 1px; background: #1E2830; margin-bottom: 28px; }
        .auth-field { margin-bottom: 16px; }
        .auth-label { display: block; font-size: 13px; font-weight: 600; color: #94A3B8; margin-bottom: 7px; }
        .auth-input {
            width: 100%; background: #0F151C;
            border: 1px solid #2A3440; border-radius: 8px;
            padding: 11px 14px; font-size: 14px; color: #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        .auth-input:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.12); }
        .auth-input::placeholder { color: #374151; }
        .auth-input-error { border-color: #EF4444 !important; }
        .auth-error-msg { margin-top: 5px; font-size: 12px; color: #EF4444; }
        .auth-btn {
            width: 100%; padding: 12px; background: #8B5CF6;
            border: none; border-radius: 8px; color: #fff;
            font-weight: 700; font-size: 14px; cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background 0.2s, transform 0.1s; margin-top: 8px;
        }
        .auth-btn:hover { background: #7C3AED; }
        .auth-btn:active { transform: scale(0.98); }
        .auth-links { margin-top: 22px; display: flex; flex-direction: column; gap: 10px; text-align: center; }
        .auth-links p { font-size: 13px; color: #6B7280; }
        .auth-links a { color: #8B5CF6; font-weight: 600; text-decoration: none; }
        .auth-links a:hover { color: #A78BFA; }
        .auth-link-btn {
            display: block; padding: 9px;
            border: 1px solid #2A3440; border-radius: 8px;
            font-size: 13px; color: #9CA3AF; text-decoration: none;
            transition: border-color 0.2s, color 0.2s;
        }
        .auth-link-btn:hover { border-color: #8B5CF6; color: #8B5CF6; }
        .auth-alert-error {
            background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; color: #FCA5A5; margin-bottom: 18px;
        }
        .auth-alert-success {
            background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; color: #6EE7B7; margin-bottom: 18px;
        }
        .auto-dismiss {
            transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), max-height 0.35s ease, margin 0.35s ease, padding 0.35s ease;
            overflow: hidden;
            max-height: 160px;
        }
        .auto-dismiss.dismissed {
            opacity: 0 !important;
            transform: translateY(-4px) !important;
            max-height: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-color: transparent !important;
        }
        .social-divider {
            display: flex; align-items: center; text-align: center;
            margin: 20px 0 16px;
        }
        .social-divider::before, .social-divider::after {
            content: ''; flex: 1; border-bottom: 1px solid #1E2830;
        }
        .social-divider span {
            padding: 0 12px; font-size: 11px; text-transform: uppercase;
            letter-spacing: 0.05em; color: #64748B; font-weight: 600;
        }
        .social-btns {
            display: flex; flex-direction: column; gap: 9px;
        }
        .social-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 10px 14px; background: #0F151C; border: 1px solid #2A3440;
            border-radius: 8px; color: #E2E8F0; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all 0.15s ease;
        }
        .social-btn:hover {
            background: #19222D; border-color: #8B5CF6; color: #fff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-brand">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra">
            <span class="auth-brand-name">Spectra</span>
        </div>

        <p class="auth-title">Sign in to your account</p>

        <div class="auth-divider"></div>

        @if(session('account_created'))
            <div id="account-created-msg" class="auth-alert-success">{{ session('account_created') }}</div>
        @endif
        @if($errors->has('email') && !$errors->has('name'))
            <div class="auth-alert-error">{{ $errors->first('email') }}</div>
        @endif
        @if($errors->has('google'))
            <div class="auth-alert-error">{{ $errors->first('google') }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="email">Email</label>
                <input
                    id="email" name="email" type="email"
                    class="auth-input {{ $errors->has('email') ? 'auth-input-error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="Enter email"
                    autocomplete="email" autofocus required
                >
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    class="auth-input {{ $errors->has('password') ? 'auth-input-error' : '' }}"
                    placeholder="Enter password"
                    autocomplete="current-password" required
                >
                @error('password')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="social-divider">
            <span>Or continue with</span>
        </div>

        <div class="social-btns">
            <a href="{{ route('auth.google') }}" class="social-btn">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </a>
        </div>

        <div class="auth-links">
            <p>No account? <a href="{{ route('register') }}?role=reporter">Create one</a></p>
        </div>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.auth-alert-error, .auth-alert-success, .auth-error-msg');
    if (alerts.length > 0) {
        alerts.forEach(function(el) { el.classList.add('auto-dismiss'); });
        setTimeout(function() {
            alerts.forEach(function(el) {
                el.classList.add('dismissed');
                setTimeout(function() { el.style.display = 'none'; }, 360);
            });
        }, 1500);
    }
});
</script>
</body>
</html>
