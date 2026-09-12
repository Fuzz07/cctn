<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BCTVI Bantayan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.7)), url('{{ asset('assets/images/login-bg.jpg') }}') center / cover no-repeat fixed;
            min-height: 100vh; overflow-x: hidden; margin: 0;
            font-family: var(--font-body);
        }
        .auth-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            padding: 5rem 1rem 3rem;
            box-sizing: border-box;
        }
        /* Side panel retired: the office photo is now the full-page background */
        .auth-left { display: none; }
        .auth-left-content { padding: 4rem 4rem 6rem 4rem; position: relative; z-index: 2; }
        .auth-logo { display: flex; align-items: center; justify-content: center; gap: 0.6rem; text-decoration: none; margin-bottom: 1.5rem; }
        .auth-logo-img { width: 44px; height: 44px; object-fit: contain; }
        .auth-logo-name { font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #ffffff; display: block; line-height: 1; text-shadow: 0 2px 8px rgba(0,0,0,0.4); }
        .auth-logo-sub { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.15em; color: #e2e8f0; text-transform: uppercase; }
        .auth-title { font-family: var(--font-heading); font-size: 3rem; font-weight: 800; line-height: 1.1; color: #ffffff; margin-bottom: 1.5rem; text-shadow: 0 2px 14px rgba(0,0,0,0.45); }
        .auth-subtitle { color: #e2e8f0; font-size: 1.05rem; line-height: 1.6; max-width: 420px; margin-bottom: 2.5rem; text-shadow: 0 1px 8px rgba(0,0,0,0.4); }
        .auth-badges { display: flex; flex-direction: column; gap: 1rem; max-width: 280px; }
        .auth-badge { display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.94); border: 1px solid rgba(255,255,255,0.5); padding: 0.75rem 1.25rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .auth-badge-icon { color: var(--primary); }
        .auth-badge-text strong { display: block; font-size: 0.9rem; color: var(--text-dark); }
        .auth-badge-text span { font-size: 0.75rem; color: var(--text-muted); }
        .auth-image { display: none; }
        .auth-right { width: 100%; max-width: 440px; background: transparent; display: flex; flex-direction: column; align-items: center; justify-content: center; position: static; padding: 0; }
        .auth-back-link { position: fixed; top: 1.5rem; right: 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; font-weight: 700; font-size: 0.88rem; transition: color 0.2s; z-index: 10; text-shadow: 0 1px 6px rgba(0,0,0,0.4); }
        .auth-back-link:hover { color: #ffffff; }
        .auth-form-card { background: #fff; width: 100%; max-width: 440px; border-radius: 20px; padding: 3rem 2.5rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); border: 1px solid #e5e7eb; position: relative; z-index: 2; }
        .auth-avatar { width: 64px; height: 64px; background: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; margin: 0 auto 1.5rem; }
        .auth-form-title { text-align: center; font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
        .auth-form-sub { text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .auth-input-group { margin-bottom: 1.25rem; }
        .auth-input-group label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem; }
        .auth-input-wrap { position: relative; }
        .auth-input-wrap svg { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        .auth-input { width: 100%; padding: 0.8rem 1rem 0.8rem 2.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: var(--text-dark); transition: border-color 0.2s; box-sizing: border-box;}
        .auth-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
        .auth-input-wrap .eye-icon { left: auto; right: 1rem; cursor: pointer; }
        .auth-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.85rem; }
        .auth-checkbox { display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); cursor: pointer; }
        .auth-checkbox input { accent-color: var(--primary); width: 16px; height: 16px; }
        .auth-forgot { color: var(--primary); font-weight: 600; text-decoration: none; }
        .btn-auth-primary { width: 100%; background: var(--primary); color: #fff; padding: 0.85rem; border-radius: 8px; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
        .auth-or { display: flex; align-items: center; text-align: center; color: #94a3b8; font-size: 0.85rem; margin: 1.5rem 0; }
        .auth-or::before, .auth-or::after { content: ''; flex: 1; border-bottom: 1px solid #e2e8f0; }
        .auth-or:not(:empty)::before { margin-right: .5em; }
        .auth-or:not(:empty)::after { margin-left: .5em; }
        .btn-auth-outline { width: 100%; background: #fff; color: var(--text-dark); padding: 0.85rem; border-radius: 8px; font-weight: 700; font-size: 1rem; border: 1px solid #cbd5e1; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.5rem; text-decoration: none; box-sizing: border-box;}
        .auth-help { text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.5rem; }
        .auth-help a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .auth-copyright { position: static; margin-top: 1.5rem; color: #e2e8f0; font-size: 0.75rem; text-align: center; text-shadow: 0 1px 6px rgba(0,0,0,0.4); }

        .bottom-bar { display: none; }

        @media (max-width: 768px) {
            .auth-layout { padding: 4.5rem 1rem 2.5rem; }
            .auth-form-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="auth-layout">
    <div class="auth-left">
        <div class="auth-left-content">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="auth-logo-img">
                <div>
                    <span class="auth-logo-name">BCTVI</span>
                    <span class="auth-logo-sub">Bantayan</span>
                </div>
            </a>
            
            <h1 class="auth-title">
                <span class="text-primary" style="color: #dc2626;">BCTVI</span><br>
                FIBER WIFI<br>
                INSTALLATION &amp; BOOKING
            </h1>
            <p class="auth-subtitle">
                Experience blazing-fast, reliable unlimited fiber internet. Book your WiFi installation appointment online in minutes!
            </p>
            
            <div class="auth-badges">
                <div class="auth-badge">
                    <svg class="auth-badge-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><circle cx="12" cy="20" r="1"/></svg>
                    <div class="auth-badge-text">
                        <strong>High-Speed Fiber WiFi</strong>
                        <span>Up to 150 Mbps</span>
                    </div>
                </div>
                <div class="auth-badge">
                    <svg class="auth-badge-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <div class="auth-badge-text">
                        <strong>Rapid Installation</strong>
                        <span>Book your preferred date & time</span>
                    </div>
                </div>
                <div class="auth-badge">
                    <svg class="auth-badge-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    <div class="auth-badge-text">
                        <strong>Fast &amp; Easy Booking</strong>
                        <span>Book online in just a few clicks</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="auth-image">
            <img src="{{ asset('assets/images/hero-router.png') }}" alt="Router">
        </div>
    </div>
    
    <div class="auth-right">
        <a href="{{ route('home') }}" class="auth-back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Home
        </a>

        <a href="{{ route('home') }}" class="auth-logo">
            <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="auth-logo-img">
            <div>
                <span class="auth-logo-name">BCTVI</span>
                <span class="auth-logo-sub">Bantayan</span>
            </div>
        </a>

        <div class="auth-form-card">
            <div class="auth-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h2 class="auth-form-title">Client Login</h2>
            <p class="auth-form-sub">Welcome back! Please sign in to continue.</p>

            @if (session('success_message'))
                <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem;">
                    {{ session('success_message') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="auth-input-group">
                    <label>Email or Username</label>
                    <div class="auth-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" name="login_input" class="auth-input" placeholder="Enter your email or username" value="{{ old('login_input') }}" required>
                    </div>
                </div>

                <div class="auth-input-group">
                    <label>Password</label>
                    <div class="auth-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" name="password" id="auth-password" class="auth-input" placeholder="Enter your password" required>
                        <svg class="eye-icon" id="toggle-pass" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </div>
                </div>

                <div class="auth-options">
                    <label class="auth-checkbox">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="{{ route('forgot-password') }}" class="auth-forgot">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-auth-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    Sign In
                </button>

                <div class="auth-or">or</div>

                <a href="{{ route('google.redirect') }}" class="btn-auth-outline" style="margin-bottom: 0.75rem;">
                    <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.223 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                    Continue with Google
                </a>

                <a href="{{ route('register') }}" class="btn-auth-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Create an Account
                </a>

            </form>
        </div>
        
        <div class="auth-copyright">
            &copy; {{ date('Y') }} BCTVI Bantayan. All rights reserved.
        </div>
    </div>
</div>

<script>
    document.getElementById('toggle-pass').addEventListener('click', function() {
        var passInput = document.getElementById('auth-password');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            this.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        } else {
            passInput.type = 'password';
            this.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        }
    });
</script>
</body>
</html>
