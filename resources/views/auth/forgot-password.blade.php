<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BCTVI Bantayan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background:#f8fafc; min-height:100vh; display:flex; align-items:center; justify-content:center; margin:0; font-family:system-ui,sans-serif; padding:1.5rem; }
        .card { background:#fff; border-radius:20px; padding:3rem 2.5rem; max-width:440px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.04); border:1px solid #e5e7eb; }
        .logo { display:flex; align-items:center; gap:.6rem; text-decoration:none; justify-content:center; margin-bottom:2rem; }
        .logo-img { width:44px; height:44px; object-fit:contain; }
        .logo-name { font-size:1.5rem; font-weight:800; color:#dc2626; }
        .logo-sub { font-size:.7rem; font-weight:600; letter-spacing:.15em; color:#64748b; text-transform:uppercase; }
        .icon-wrap { width:72px; height:72px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#dc2626; margin:0 auto 1.5rem; }
        .title { font-size:1.5rem; font-weight:800; color:#0f172a; text-align:center; margin-bottom:.5rem; }
        .subtitle { color:#64748b; font-size:.9rem; text-align:center; margin-bottom:2rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:.85rem; font-weight:700; color:#1e293b; margin-bottom:.5rem; }
        .form-input { width:100%; padding:.85rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.95rem; box-sizing:border-box; }
        .form-input:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
        .btn-submit { width:100%; background:#dc2626; color:#fff; padding:1rem; border-radius:8px; font-weight:700; font-size:1rem; border:none; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:.5rem; box-shadow:0 4px 12px rgba(220,38,38,.25); margin-top:.5rem; }
        .btn-submit:hover { background:#b91c1c; }
        .back-link { display:flex; justify-content:center; margin-top:1.5rem; }
        .back-link a { color:#64748b; font-size:.88rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:.35rem; }
        .back-link a:hover { color:#dc2626; }
        .alert-success { background:#f0fdf4; color:#15803d; padding:.75rem 1rem; border-radius:8px; font-size:.9rem; margin-bottom:1.5rem; border:1px solid #bbf7d0; font-weight:500; }
        .alert-error { background:#fef2f2; color:#991b1b; padding:.75rem 1rem; border-radius:8px; font-size:.9rem; margin-bottom:1.5rem; border:1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="card">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="logo-img">
            <div>
                <span class="logo-name">BCTVI</span>
                <span class="logo-sub">Bantayan</span>
            </div>
        </a>

        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>

        <h1 class="title">Forgot Password</h1>
        <p class="subtitle">Enter your registered email address and we'll send you reset instructions.</p>

        @if(session('success_message'))
            <div class="alert-success">{{ session('success_message') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('forgot-password.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="your@email.com" value="{{ old('email') }}" required>
            </div>
            <button type="submit" class="btn-submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                Send Reset Instructions
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
