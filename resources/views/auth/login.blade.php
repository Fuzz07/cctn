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

        /* ── Terms & Conditions Checkbox & Note ── */
        .auth-terms {
            margin-bottom: 1.25rem;
            padding: 0.75rem 0.85rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .auth-terms:hover, .auth-terms:focus-within {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }
        .terms-label {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            font-size: 0.82rem;
            color: #475569;
            cursor: pointer;
            line-height: 1.45;
            user-select: none;
            margin: 0;
        }
        .terms-label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 17px;
            height: 17px;
            margin-top: 1px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .terms-link {
            color: var(--primary);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
            cursor: pointer;
            transition: color 0.15s;
        }
        .terms-link:hover {
            color: #b91c1c;
        }
        .auth-terms-note {
            text-align: center;
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 1.25rem;
            line-height: 1.5;
        }
        .auth-terms-note a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
        }

        /* ── Terms Modal ── */
        .terms-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            box-sizing: border-box;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .terms-modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }
        .terms-modal-container {
            background: #ffffff;
            width: 100%;
            max-width: 620px;
            max-height: 85vh;
            border-radius: 18px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transform: scale(0.95) translateY(10px);
            transition: transform 0.25s ease;
        }
        .terms-modal-backdrop.active .terms-modal-container {
            transform: scale(1) translateY(0);
        }
        .terms-modal-head {
            padding: 1.25rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .terms-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .terms-modal-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .terms-modal-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
            font-family: var(--font-heading);
            line-height: 1.2;
        }
        .terms-modal-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
            line-height: 1.3;
        }
        .terms-modal-close {
            background: #f1f5f9;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .terms-modal-close:hover {
            background: #fee2e2;
            color: #dc2626;
        }
        .terms-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            color: #334155;
            font-size: 0.88rem;
            line-height: 1.65;
            flex: 1;
        }
        .terms-modal-notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
            line-height: 1.45;
        }
        .terms-modal-body h4 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
            margin: 1.15rem 0 0.35rem 0;
        }
        .terms-modal-body h4:first-of-type {
            margin-top: 0;
        }
        .terms-modal-body p {
            margin: 0 0 0.75rem 0;
            color: #475569;
        }
        .terms-modal-foot {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .terms-full-page-link {
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: color 0.15s;
        }
        .terms-full-page-link:hover {
            color: #dc2626;
        }
        .terms-modal-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-left: auto;
        }
        .btn-terms-modal-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 0.6rem 1.1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-terms-modal-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .btn-terms-modal-primary {
            background: #dc2626;
            border: 1px solid #dc2626;
            color: #ffffff;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(220,38,38,0.25);
        }
        .btn-terms-modal-primary:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        @media (max-width: 768px) {
            .auth-layout { padding: 4.5rem 1rem 2.5rem; }
            .auth-form-card { padding: 2rem 1.5rem; }
            .terms-modal-actions { width: 100%; justify-content: flex-end; }
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

                <div class="auth-terms">
                    <label class="terms-label" for="agree_terms">
                        <input type="checkbox" name="agree_terms" id="agree_terms" value="1" required {{ old('agree_terms') ? 'checked' : '' }}>
                        <span class="terms-text">
                            I agree to the <a href="javascript:void(0)" id="openTermsModal" class="terms-link">Terms and Conditions</a>
                        </span>
                    </label>
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

                <p class="auth-terms-note">
                    By signing in, you acknowledge and agree to BCTVI's <a href="javascript:void(0)" class="open-terms-trigger">Terms and Conditions</a> &amp; Privacy Policy.
                </p>

            </form>
        </div>
        
        <div class="auth-copyright">
            &copy; {{ date('Y') }} BCTVI Bantayan. All rights reserved.
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div id="termsModal" class="terms-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="termsModalTitle">
    <div class="terms-modal-container">
        <div class="terms-modal-head">
            <div class="terms-modal-title-wrap">
                <div class="terms-modal-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h3 id="termsModalTitle" class="terms-modal-title">Terms &amp; Conditions</h3>
                    <p class="terms-modal-sub">BCTVI Broadband Telecommunications • Client Agreement</p>
                </div>
            </div>
            <button type="button" class="terms-modal-close" id="closeTermsBtn" aria-label="Close Terms Modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="terms-modal-body">
            <div class="terms-modal-notice">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Please review these terms before signing in to manage your fiber account.</span>
            </div>

            <h4>1. Agreement Overview &amp; Acceptance</h4>
            <p>By signing in to your BCTVI client account or booking an appointment, you agree to abide by these Terms and Conditions and our service policies across Bantayan Island (Bantayan, Santa Fe, and Madridejos).</p>

            <h4>2. Client Account &amp; Credential Security</h4>
            <p>Clients are solely responsible for maintaining the confidentiality of their credentials (username/email and password). Any activity conducted through your authenticated portal session is deemed authorized by you.</p>

            <h4>3. Fiber WiFi Installation &amp; Site Feasibility</h4>
            <p>All online booking requests are subject to physical facility availability, optical distribution point (ODP) capacity, and technical feasibility. Clients authorize certified BCTVI technicians to access the premises for cabling and terminal setup.</p>

            <h4>4. Billing, Due Dates &amp; Disconnection Policy</h4>
            <p>Monthly subscription fees must be paid on or before the due date specified on each statement of account. Accounts overdue by more than five (5) days may experience automated temporary disconnection until settled.</p>

            <h4>5. Acceptable Use Policy (AUP)</h4>
            <p>The fiber connection must be used strictly for legitimate purposes. Any unlawful activity, unauthorized reselling, spamming, or actions endangering network performance or security are strictly prohibited.</p>

            <h4>6. Equipment Ownership &amp; Care</h4>
            <p>Optical Network Terminals (modems), power adapters, and drop cables supplied by BCTVI remain property of BCTVI unless purchased. Subscribers agree to exercise proper care and must not attempt unauthorized cable splicing.</p>

            <h4>7. Privacy &amp; Data Protection (RA 10173)</h4>
            <p>Under the Philippine Data Privacy Act of 2012, your personal information and uploaded proofs of billing are kept secure, confidential, and used strictly for account administration, billing, and technical notifications.</p>

            <h4>8. Customer Support &amp; Hotline</h4>
            <p>For inquiries, billing disputes, or connection troubleshooting, our Bantayan Island customer support team is available at <strong>0999 998 8209</strong> or via the online portal chat assistant.</p>
        </div>

        <div class="terms-modal-foot">
            <a href="{{ route('terms') }}" target="_blank" class="terms-full-page-link">
                <span>View Full Legal Document</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <div class="terms-modal-actions">
                <button type="button" class="btn-terms-modal-secondary" id="declineTermsBtn">Close</button>
                <button type="button" class="btn-terms-modal-primary" id="acceptTermsBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    I Accept Terms
                </button>
            </div>
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

    // ── Terms and Conditions Modal Handler ──
    (function() {
        var modal = document.getElementById('termsModal');
        var openBtn = document.getElementById('openTermsModal');
        var triggers = document.querySelectorAll('.open-terms-trigger');
        var closeBtn = document.getElementById('closeTermsBtn');
        var declineBtn = document.getElementById('declineTermsBtn');
        var acceptBtn = document.getElementById('acceptTermsBtn');
        var termsCheckbox = document.getElementById('agree_terms');

        function openModal() {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        triggers.forEach(function(el) { el.addEventListener('click', openModal); });
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (declineBtn) declineBtn.addEventListener('click', closeModal);

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                if (termsCheckbox) {
                    termsCheckbox.checked = true;
                    // Add subtle visual feedback
                    var termsCard = termsCheckbox.closest('.auth-terms');
                    if (termsCard) {
                        termsCard.style.borderColor = '#16a34a';
                        termsCard.style.backgroundColor = '#f0fdf4';
                    }
                }
                closeModal();
            });
        }

        // Close on clicking backdrop outside modal content
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    })();
</script>
</body>
</html>
