<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BCTVI Bantayan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background: #f8fafc; min-height: 100vh; overflow-x: hidden; font-family: var(--font-body, sans-serif); margin: 0; }
        .admin-auth-layout { display: flex; min-height: 100vh; width: 100%; }
        
        .admin-auth-left { width: 50%; background: #060d1b; position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; color: #ffffff; }
        .admin-auth-left-bg { position: absolute; inset: 0; z-index: 0; }
        .admin-auth-left-bg img { width: 100%; height: 100%; object-fit: cover; opacity: 0.65; }
        .admin-auth-left-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(6,13,27,0.85) 0%, rgba(6,13,27,0.4) 50%, rgba(6,13,27,0.92) 100%); z-index: 1; }
        
        .admin-auth-left-content { padding: 3.5rem 4rem; position: relative; z-index: 2; height: 100%; display: flex; flex-direction: column; }
        .admin-brand { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; margin-bottom: auto; }
        .admin-brand img { width: 48px; height: 48px; object-fit: contain; }
        .admin-brand-text strong { display: block; font-family: var(--font-heading, sans-serif); font-size: 1.5rem; font-weight: 800; color: #ffffff; line-height: 1; margin-bottom: 0.15rem; }
        .admin-brand-text span { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.1em; color: rgba(255,255,255,0.7); text-transform: uppercase; }

        .admin-hero-text { margin-bottom: 3rem; }
        .admin-hero-title { font-family: var(--font-heading, sans-serif); font-size: 3.5rem; font-weight: 800; line-height: 1.05; color: #ffffff; margin-bottom: 1.5rem; }
        .admin-hero-title span { color: #ef4444; }
        .admin-hero-sub { color: rgba(255,255,255,0.8); font-size: 1.1rem; line-height: 1.6; max-width: 460px; }

        .admin-system-status { display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; max-width: 400px; backdrop-filter: blur(10px); }
        .status-indicator { width: 12px; height: 12px; background: #10b981; border-radius: 50%; box-shadow: 0 0 12px rgba(16,185,129,0.8); }
        .status-text strong { display: block; font-size: 0.95rem; font-weight: 700; color: #ffffff; }
        .status-text span { font-size: 0.8rem; color: rgba(255,255,255,0.6); }

        .admin-auth-right { width: 50%; background: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; padding: 4rem 2rem; }
        
        .admin-back-btn { position: absolute; top: 2.5rem; right: 2.5rem; display: inline-flex; align-items: center; gap: 0.5rem; color: #64748b; text-decoration: none; font-weight: 700; font-size: 0.85rem; padding: 0.6rem 1rem; background: #f1f5f9; border-radius: 8px; transition: all 0.2s; z-index: 10; }
        .admin-back-btn:hover { background: #e2e8f0; color: #0f172a; }

        .admin-form-container { width: 100%; max-width: 420px; z-index: 2; }
        .admin-form-header { text-align: center; margin-bottom: 2.5rem; }
        .admin-form-icon { width: 72px; height: 72px; background: #fef2f2; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #dc2626; margin: 0 auto 1.5rem; transform: rotate(-5deg); box-shadow: 0 10px 25px rgba(220,38,38,0.15); }
        .admin-form-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; font-family: var(--font-heading, sans-serif); }
        .admin-form-sub { color: #64748b; font-size: 0.95rem; }

        .admin-input-group { margin-bottom: 1.5rem; }
        .admin-input-group label { display: block; font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
        .admin-input-wrap { position: relative; }
        .admin-input-wrap svg { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .admin-input { width: 100%; padding: 1rem 1rem 1rem 3rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; color: #0f172a; background: #f8fafc; transition: all 0.2s; font-weight: 500; box-sizing: border-box; }
        .admin-input:focus { border-color: #dc2626; background: #ffffff; outline: none; box-shadow: 0 0 0 4px rgba(220,38,38,0.1); }
        
        .btn-admin-submit { width: 100%; background: #0f172a; color: #ffffff; padding: 1.1rem; border-radius: 12px; font-weight: 700; font-size: 1.05rem; border: none; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.75rem; box-shadow: 0 10px 25px rgba(15,23,42,0.2); transition: all 0.2s; margin-top: 1rem; }
        .btn-admin-submit:hover { background: #1e293b; transform: translateY(-2px); box-shadow: 0 15px 30px rgba(15,23,42,0.3); }
        .btn-admin-submit svg { transition: transform 0.2s; }
        .btn-admin-submit:hover svg { transform: translateX(4px); }

        .admin-footer { position: absolute; bottom: 2rem; color: #94a3b8; font-size: 0.8rem; text-align: center; width: 100%; font-weight: 500; }

        @media (max-width: 1024px) {
            .admin-auth-left { display: none; }
            .admin-auth-right { width: 100%; }
        }
    </style>
</head>
<body>
<div class="admin-auth-layout">
    <!-- LEFT PANEL -->
    <div class="admin-auth-left">
        <div class="admin-auth-left-bg">
            <img src="{{ asset('assets/images/login-bg.jpg') }}" alt="BCTVI Office">
        </div>
        <div class="admin-auth-left-overlay"></div>
        
        <div class="admin-auth-left-content">
            <a href="{{ route('home') }}" class="admin-brand">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI">
                <div class="admin-brand-text">
                    <strong>BCTVI</strong>
                    <span>Bantayan</span>
                </div>
            </a>
            
            <div class="admin-hero-text">
                <h1 class="admin-hero-title">
                    Administrative<br>
                    <span>Command</span> Center
                </h1>
                <p class="admin-hero-sub">
                    Secure portal for managing client appointments, technical dispatches, billing operations, and system-wide settings.
                </p>
            </div>
            
            <div class="admin-system-status">
                <div class="status-indicator"></div>
                <div class="status-text">
                    <strong>System Operational</strong>
                    <span>All services running normally</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT PANEL -->
    <div class="admin-auth-right">
        <a href="{{ route('home') }}" class="admin-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Public Site
        </a>
        
        <div class="admin-form-container">
            <div class="admin-form-header">
                <div class="admin-form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M8 11h8"/><path d="M12 15V7"/></svg>
                </div>
                <h2 class="admin-form-title">Secure Access</h2>
                <p class="admin-form-sub">Enter your credentials to access the admin portal.</p>
            </div>

            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: 12px; font-size: 0.9rem; margin-bottom: 1.5rem; font-weight: 500;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                <div class="admin-input-group">
                    <label>Administrator ID</label>
                    <div class="admin-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" name="username" class="admin-input" placeholder="Enter admin username" value="{{ old('username') }}" required autofocus>
                    </div>
                </div>

                <div class="admin-input-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                        <label style="margin:0;">Master Password</label>
                    </div>
                    <div class="admin-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" name="password" id="admin-pass" class="admin-input" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-admin-submit">
                    Authenticate Session
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
        </div>
        
        <div class="admin-footer">
            &copy; {{ date('Y') }} BCTVI Bantayan Administrator Portal. Restricted Access.
        </div>
    </div>
</div>
</body>
</html>
