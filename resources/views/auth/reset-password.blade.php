<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BCTVI Bantayan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background:#f8fafc; min-height:100vh; display:flex; align-items:center; justify-content:center; margin:0; font-family:system-ui,sans-serif; padding:1.5rem; }
        .card { background:#fff; border-radius:20px; padding:3rem 2.5rem; max-width:440px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,.04); border:1px solid #e5e7eb; }
        .logo { display:flex; align-items:center; gap:.6rem; text-decoration:none; justify-content:center; margin-bottom:2rem; }
        .logo-img { width:44px; height:44px; object-fit:contain; }
        .logo-name { font-size:1.5rem; font-weight:800; color:#dc2626; }
        .logo-sub { display:block; font-size:.7rem; font-weight:600; letter-spacing:.15em; color:#64748b; text-transform:uppercase; }
        .title { font-size:1.5rem; font-weight:800; color:#0f172a; text-align:center; margin-bottom:.5rem; }
        .subtitle { color:#64748b; font-size:.9rem; text-align:center; margin-bottom:2rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:.85rem; font-weight:700; color:#1e293b; margin-bottom:.5rem; }
        .form-input { width:100%; padding:.85rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.95rem; box-sizing:border-box; }
        .form-input:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
        .btn-submit { width:100%; background:#dc2626; color:#fff; padding:1rem; border-radius:8px; font-weight:700; font-size:1rem; border:0; cursor:pointer; box-shadow:0 4px 12px rgba(220,38,38,.25); }
        .btn-submit:hover { background:#b91c1c; }
        .alert-error { background:#fef2f2; color:#991b1b; padding:.75rem 1rem; border-radius:8px; font-size:.9rem; margin-bottom:1.5rem; border:1px solid #fecaca; }
        .back-link { display:block; color:#64748b; font-size:.88rem; font-weight:600; text-decoration:none; text-align:center; margin-top:1.5rem; }
        .back-link:hover { color:#dc2626; }
    </style>
</head>
<body>
    <div class="card">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="logo-img">
            <div><span class="logo-name">BCTVI</span><span class="logo-sub">Bantayan</span></div>
        </a>

        <h1 class="title">Create a New Password</h1>
        <p class="subtitle">Enter and confirm your new password below.</p>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" name="email" class="form-input" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">New Password</label>
                <input id="password" type="password" name="password" class="form-input" minlength="8" autocomplete="new-password" required autofocus>
            </div>
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" minlength="8" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn-submit">Reset Password</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">Back to Login</a>
    </div>
</body>
</html>
