<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectra — Administrator Sign In</title>
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
        .admin-badge {
            display: inline-block;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            padding: 3px 10px; border-radius: 20px;
            background: rgba(139,92,246,0.1);
            color: #A78BFA;
            border: 1px solid rgba(139,92,246,0.25);
            margin-bottom: 28px;
            align-self: center;
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="display:flex;flex-direction:column;">

        <div class="auth-brand">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra">
            <span class="auth-brand-name">Spectra</span>
        </div>

        <p class="auth-title" style="margin-bottom:16px">Sign in to your account</p>
        <span class="admin-badge">Administrator</span>

        <div class="auth-divider"></div>

        @if(session('account_created'))
            <div id="account-created-msg" class="auth-alert-success">{{ session('account_created') }}</div>
            <script>setTimeout(function(){ var el = document.getElementById('account-created-msg'); if(el) el.style.display='none'; }, 1000);</script>
        @endif
        @if(session('success'))
            <div class="auth-alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->has('email') && !$errors->has('name'))
            <div class="auth-alert-error">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="email">Email</label>
                <input
                    id="email" name="email" type="email"
                    class="auth-input {{ $errors->has('email') ? 'auth-input-error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="admin@example.com"
                    autocomplete="email" autofocus required
                >
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    class="auth-input {{ $errors->has('password') ? 'auth-input-error' : '' }}"
                    placeholder="Enter your password"
                    autocomplete="current-password" required
                >
                @error('password')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-links">
            <p>Need an account? <a href="{{ route('register') }}?role=investigator">Register</a></p>
            <a href="{{ route('login') }}" class="auth-link-btn">Sign in as Reporter</a>
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
