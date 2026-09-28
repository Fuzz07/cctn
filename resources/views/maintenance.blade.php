<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Under Maintenance – BCTVI Bantayan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            overflow: hidden;
            position: relative;
        }

        /* Animated background blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: blobFloat 8s ease-in-out infinite alternate;
        }
        .blob-1 { width: 500px; height: 500px; background: #dc2626; top: -150px; left: -100px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: #7c3aed; bottom: -100px; right: -80px; animation-delay: -3s; }
        .blob-3 { width: 300px; height: 300px; background: #0891b2; top: 50%; left: 50%; transform: translate(-50%,-50%); animation-delay: -6s; }

        @keyframes blobFloat {
            from { transform: scale(1) translate(0, 0); }
            to   { transform: scale(1.1) translate(20px, -20px); }
        }

        /* Card */
        .card {
            position: relative;
            z-index: 10;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 90%;
            text-align: center;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Gear icon with spin */
        .icon-wrap {
            width: 80px; height: 80px;
            margin: 0 auto 1.75rem;
            background: rgba(220,38,38,0.15);
            border: 2px solid rgba(220,38,38,0.35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gear-icon {
            animation: spinGear 6s linear infinite;
            color: #ef4444;
        }
        @keyframes spinGear {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        h1 {
            font-size: 2rem;
            font-weight: 900;
            color: #f8fafc;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
        }
        h1 span { color: #ef4444; }

        p {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        /* Progress bar */
        .progress-wrap {
            background: rgba(255,255,255,0.08);
            border-radius: 99px;
            height: 6px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #dc2626, #f97316);
            border-radius: 99px;
            animation: progressPulse 2.5s ease-in-out infinite;
        }
        @keyframes progressPulse {
            0%   { width: 30%; opacity: 1; }
            50%  { width: 80%; opacity: 0.7; }
            100% { width: 30%; opacity: 1; }
        }

        /* Admin link */
        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            background: rgba(220,38,38,0.15);
            border: 1px solid rgba(220,38,38,0.3);
            color: #fca5a5;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }
        .admin-link:hover {
            background: #dc2626;
            color: #fff;
            border-color: #dc2626;
        }

        .footer-text {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.25);
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="card">
        <div class="icon-wrap">
            <svg class="gear-icon" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </div>

        <h1>We'll be back <span>soon.</span></h1>
        <p>
            BCTVI Bantayan is currently undergoing scheduled maintenance.<br>
            We're working hard to get things back up for you. Thank you for your patience.
        </p>

        <div class="progress-wrap">
            <div class="progress-bar"></div>
        </div>

        <a href="{{ route('admin.login') }}" class="admin-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Admin Login
        </a>

        <div class="footer-text">&copy; {{ date('Y') }} BCTVI Bantayan. All rights reserved.</div>
    </div>
</body>
</html>
