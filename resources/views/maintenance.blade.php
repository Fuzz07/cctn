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

        :root {
            --red:    #dc2626;
            --red-h:  #b91c1c;
            --red-s:  #ef4444;
            --black:  #0a0a0a;
            --dark:   #111111;
            --card:   #181818;
            --border: rgba(220,38,38,0.25);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--black);
            overflow: hidden;
            position: relative;
        }

        /* ── Grid lines bg ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(220,38,38,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(220,38,38,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Red glow blobs ── */
        .glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }
        .glow-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(220,38,38,0.18) 0%, transparent 70%);
            top: -200px; left: -150px;
            animation: glowDrift 10s ease-in-out infinite alternate;
        }
        .glow-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(220,38,38,0.12) 0%, transparent 70%);
            bottom: -150px; right: -100px;
            animation: glowDrift 14s ease-in-out infinite alternate-reverse;
        }
        @keyframes glowDrift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, -30px) scale(1.08); }
        }

        /* ── Wrapper ── */
        .wrapper {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 540px;
            animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Top brand bar ── */
        .brand-bar {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .brand-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--red);
            box-shadow: 0 0 10px var(--red);
            animation: pulseDot 1.6s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%,100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(1.6); opacity: 0.5; }
        }
        .brand-label {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--red);
        }

        /* ── Card ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3rem 2.5rem 2.5rem;
            text-align: center;
            box-shadow:
                0 0 0 1px rgba(220,38,38,0.08),
                0 40px 80px rgba(0,0,0,0.6),
                inset 0 1px 0 rgba(255,255,255,0.04);
            position: relative;
            overflow: hidden;
        }

        /* Red top border accent */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--red), transparent);
            border-radius: 0 0 2px 2px;
        }

        /* ── Gear icon ── */
        .icon-ring {
            width: 88px; height: 88px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            background: rgba(220,38,38,0.08);
            border: 1.5px solid rgba(220,38,38,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        /* Outer rotating ring */
        .icon-ring::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 1.5px dashed rgba(220,38,38,0.2);
            animation: ringRotate 8s linear infinite;
        }
        @keyframes ringRotate {
            to { transform: rotate(360deg); }
        }
        .gear {
            color: var(--red-s);
            animation: gearSpin 5s linear infinite;
            filter: drop-shadow(0 0 8px rgba(239,68,68,0.5));
        }
        @keyframes gearSpin {
            to { transform: rotate(360deg); }
        }

        /* ── Typography ── */
        h1 {
            font-size: 2.1rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 0.9rem;
        }
        h1 .accent {
            color: var(--red-s);
            position: relative;
        }

        p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.45);
            line-height: 1.75;
            margin-bottom: 2rem;
        }

        /* ── Progress bar ── */
        .bar-wrap {
            background: rgba(255,255,255,0.05);
            border-radius: 99px;
            height: 4px;
            overflow: hidden;
            margin-bottom: 0.6rem;
            border: 1px solid rgba(255,255,255,0.04);
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--red-h), var(--red-s));
            border-radius: 99px;
            box-shadow: 0 0 10px rgba(220,38,38,0.6);
            animation: barPulse 2.8s ease-in-out infinite;
        }
        @keyframes barPulse {
            0%   { width: 25%; }
            50%  { width: 78%; }
            100% { width: 25%; }
        }
        .bar-label {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.2);
            text-align: right;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        /* ── Admin button ── */
        .admin-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.8rem 1.75rem;
            border-radius: 10px;
            background: var(--red);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: 0.02em;
            border: none;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
            box-shadow: 0 4px 20px rgba(220,38,38,0.4);
        }
        .admin-btn:hover {
            background: var(--red-h);
            box-shadow: 0 6px 28px rgba(220,38,38,0.6);
            transform: translateY(-1px);
        }
        .admin-btn:active { transform: translateY(0); }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.06);
            margin: 2rem 0 1.25rem;
        }

        /* ── Footer ── */
        .footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .footer-copy {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.18);
            font-weight: 500;
        }
        .footer-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.18);
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #f59e0b;
            box-shadow: 0 0 6px #f59e0b;
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="wrapper">

        {{-- Brand tag --}}
        <div class="brand-bar">
            <span class="brand-dot"></span>
            <span class="brand-label">BCTVI Bantayan</span>
            <span class="brand-dot"></span>
        </div>

        {{-- Card --}}
        <div class="card">

            {{-- Gear --}}
            <div class="icon-ring">
                <svg class="gear" xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06
                             a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09
                             A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83
                             l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09
                             A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83
                             l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09
                             a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83
                             l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09
                             a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </div>

            <h1>We'll be back <span class="accent">soon.</span></h1>

            <p>
                BCTVI Bantayan is currently undergoing scheduled maintenance.<br>
                We're working hard to restore full service. Thank you for your patience.
            </p>

            {{-- Progress --}}
            <div class="bar-wrap">
                <div class="bar-fill"></div>
            </div>
            <div class="bar-label">Restoring services…</div>

            {{-- Admin CTA --}}
            <a href="{{ route('admin.login') }}" class="admin-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Admin Login
            </a>

            <hr class="divider">

            <div class="footer-row">
                <span class="footer-copy">&copy; {{ date('Y') }} BCTVI Bantayan. All rights reserved.</span>
                <span class="footer-status">
                    <span class="status-dot"></span>
                    Under Maintenance
                </span>
            </div>

        </div>
    </div>
</body>
</html>
