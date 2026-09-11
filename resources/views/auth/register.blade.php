<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectra — Create Account</title>
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
            max-width: 380px;
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
        .auth-select {
            width: 100%; background: #0F151C;
            border: 1px solid #2A3440; border-radius: 8px;
            padding: 11px 14px; font-size: 14px; color: #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif; outline: none;
            cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
            transition: border-color 0.2s;
        }
        .auth-select:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.12); }
        .auth-select option { background: #151B23; }
        .auth-btn {
            width: 100%; padding: 12px; background: #8B5CF6;
            border: none; border-radius: 8px; color: #fff;
            font-weight: 700; font-size: 14px; cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background 0.2s, transform 0.1s; margin-top: 8px;
        }
        .auth-btn:hover { background: #7C3AED; }
        .auth-btn:active { transform: scale(0.98); }
        .auth-links { margin-top: 22px; text-align: center; }
        .auth-links p { font-size: 13px; color: #6B7280; }
        .auth-links a { color: #8B5CF6; font-weight: 600; text-decoration: none; }
        .auth-links a:hover { color: #A78BFA; }
        .auth-alert-error {
            background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; color: #FCA5A5; margin-bottom: 18px;
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
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-brand">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra">
            <span class="auth-brand-name">Spectra</span>
        </div>

        <p class="auth-title">Create a new account</p>

        <div class="auth-divider"></div>

        @if($errors->any())
            <div class="auth-alert-error">
                <ul style="padding-left:16px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="name">Full Name</label>
                <input
                    id="name" name="name" type="text"
                    class="auth-input {{ $errors->has('name') ? 'auth-input-error' : '' }}"
                    value="{{ old('name') }}"
                    placeholder="Juan dela Cruz"
                    autocomplete="name" autofocus required
                >
                @error('name')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-label" for="email">Email</label>
                <input
                    id="email" name="email" type="email"
                    class="auth-input {{ $errors->has('email') ? 'auth-input-error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="Enter email"
                    autocomplete="email" required
                >
                @error('email')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    class="auth-input {{ $errors->has('password') ? 'auth-input-error' : '' }}"
                    placeholder="Enter password"
                    autocomplete="new-password" required
                >
                @error('password')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation" name="password_confirmation" type="password"
                    class="auth-input"
                    placeholder="Enter password"
                    autocomplete="new-password" required
                >
            </div>

            <div class="auth-field" style="margin-top:16px;margin-bottom:18px;">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;user-select:none;">
                    <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required
                        style="margin-top:2.5px;width:16px;height:16px;accent-color:#8B5CF6;cursor:pointer;flex-shrink:0;">
                    <span style="font-size:12.5px;color:#94A3B8;line-height:1.45;">
                        I agree to the <a href="javascript:void(0)" onclick="openTermsModal()" style="color:#8B5CF6;font-weight:600;text-decoration:underline;">Terms of Use and Privacy Policy</a>.
                    </span>
                </label>
                @error('terms')
                    <div class="auth-error-msg">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <div class="auth-links" style="margin-top:22px">
            <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>

    </div>
</div>

<!-- Terms of Use and Privacy Policy Modal -->
<div id="terms-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#151B23;border:1px solid #2A3440;border-radius:14px;max-width:520px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,0.6);">
        <div style="padding:18px 22px;border-bottom:1px solid #1E2830;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:16px;font-weight:700;color:#F1F5F9;margin:0;">Terms of Use & Privacy Policy</h3>
            <button type="button" onclick="closeTermsModal()" style="background:none;border:none;color:#94A3B8;font-size:20px;cursor:pointer;padding:4px 8px;line-height:1;">&times;</button>
        </div>
        <div style="padding:22px;overflow-y:auto;font-size:13px;line-height:1.6;color:#94A3B8;space-y:12px;">
            <h4 style="color:#E2E8F0;font-size:14px;margin-bottom:6px;">1. System Purpose & Community Conduct</h4>
            <p style="margin-bottom:14px;">Spectra is a municipal GIS and incident monitoring network. By creating an account, you agree to report truthful, accurate observations and refrain from submitting fraudulent, hoax, or disruptive anomaly reports.</p>
            
            <h4 style="color:#E2E8F0;font-size:14px;margin-bottom:6px;">2. Geographic & Device Data Privacy</h4>
            <p style="margin-bottom:14px;">When you submit field reports, geographic coordinates (latitude and longitude) and optional uploaded media are securely recorded to facilitate municipal response teams in San Francisco, Agusan del Sur. Personal email addresses are never sold or shared with external third parties.</p>

            <h4 style="color:#E2E8F0;font-size:14px;margin-bottom:6px;">3. Account Security</h4>
            <p style="margin-bottom:14px;">Users are responsible for safeguarding their login credentials. Any activity conducted under your registered email falls under your primary responsibility.</p>

            <h4 style="color:#E2E8F0;font-size:14px;margin-bottom:6px;">4. Acceptance</h4>
            <p>Checking the acceptance box confirms you have read, understood, and agreed to adhere to these terms and the municipal privacy protocols.</p>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #1E2830;display:flex;justify-content:flex-end;">
            <button type="button" onclick="acceptAndCloseTerms()" style="padding:9px 18px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;">
                I Understand and Agree
            </button>
        </div>
    </div>
</div>

<script>
function openTermsModal() {
    var modal = document.getElementById('terms-modal');
    if (modal) modal.style.display = 'flex';
}
function closeTermsModal() {
    var modal = document.getElementById('terms-modal');
    if (modal) modal.style.display = 'none';
}
function acceptAndCloseTerms() {
    var checkbox = document.getElementById('terms');
    if (checkbox) checkbox.checked = true;
    closeTermsModal();
}

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
