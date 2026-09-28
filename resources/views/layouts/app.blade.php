@php
    // Android WebView app identifies itself via this user-agent marker (see MainActivity.kt)
    $isApp = str_contains(request()->userAgent() ?? '', 'CCTN-Android-App');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#dc2626">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BCTVI Broadband Telecommunications')</title>
    <meta name="description" content="Official BCTVI Broadband Client Portal & Mobile App. Book WiFi installation, manage statements, and receive installation updates.">
    @if(request()->routeIs('home'))
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('bctvi-theme');
                var isDark = savedTheme ? savedTheme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.theme = isDark ? 'dark' : 'light';
            } catch (error) {}
        })();
    </script>
    @endif
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Modern Mobile Client Navigation Drawer & Bottom Bar */
        .client-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .client-navbar {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0.75rem clamp(1.25rem, 3vw, 3rem);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .landing-header-tools {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            margin-left: 1rem;
        }
        .landing-live-date {
            color: var(--text-muted);
            font-size: 0.76rem;
            font-weight: 700;
            white-space: nowrap;
        }
        @media (max-width: 1180px) { .landing-live-date { display: none; } }
        .client-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .client-brand-img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }
        .client-brand-name {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            font-weight: 900;
            color: #dc2626;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        .client-brand-sub {
            font-size: 0.65rem;
            color: var(--text-muted);
            font-weight: 700;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* Desktop Nav Links & Buttons */
        .top-nav-link {
            position: relative;
            color: var(--text-body);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.5rem 0.15rem;
            transition: color 0.2s;
        }
        .top-nav-link:hover { color: #dc2626; }
        .top-nav-link.active { color: #dc2626; }
        .top-nav-link.active::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -4px;
            height: 2.5px;
            border-radius: 2px;
            background: #dc2626;
        }
        .top-nav-btn-outline {
            display: inline-flex;
            align-items: center;
            color: #dc2626;
            background: var(--bg-card);
            border: 1.5px solid #dc2626;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.55rem 1.6rem;
            border-radius: 50px;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .top-nav-btn-outline:hover { border-color: #b91c1c; color: #b91c1c; background: #fef2f2; }
        .top-nav-btn-solid {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #fff;
            background: #dc2626;
            border: none;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.6rem 1.6rem;
            border-radius: 50px;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .top-nav-btn-solid:hover { background: #b91c1c; color: #fff; transform: translateY(-1px); }
        .top-nav-logout-form { margin: 0; }
        .top-nav-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #ef4444;
            background: var(--bg-card);
            border: 1.5px solid #fecaca;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.55rem 1.15rem;
            border-radius: 10px;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .top-nav-logout:hover { border-color: #ef4444; color: #dc2626; background: #fef2f2; }

        /* Drawer Overlay */
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .drawer-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Client Drawer Menu */
        .client-drawer {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 290px;
            background: var(--bg-card);
            z-index: 201;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0,0,0,0.15);
        }
        .drawer-overlay.active .client-drawer {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .drawer-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .drawer-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #dc2626;
            object-fit: cover;
        }
        .drawer-user-name {
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.2;
        }
        .drawer-user-role {
            font-size: 0.75rem;
            color: var(--text-faint);
        }
        .btn-drawer-close {
            background: none;
            border: none;
            color: var(--text-faint);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 4px;
        }

        .drawer-menu {
            padding: 1rem;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .drawer-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            color: var(--text-body);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }
        .drawer-item:hover, .drawer-item.active {
            background: #fef2f2;
            color: #dc2626;
        }
        .drawer-item-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        .drawer-badge {
            margin-left: auto;
            background: #dc2626;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.15rem 0.5rem;
            border-radius: 99px;
        }

        .drawer-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-light);
            font-size: 0.8rem;
            color: var(--text-faint);
            text-align: center;
        }

        /* Bottom Navigation Bar for Mobile */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--border-light);
            z-index: 99;
            padding: 0.4rem 0.5rem 0.6rem 0.5rem;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
        }
        .bottom-nav-grid {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: 1fr;
            text-align: center;
        }
        .bottom-nav-item {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.68rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 4px 0;
        }
        .bottom-nav-item.active {
            color: #dc2626;
        }
        .bottom-nav-icon {
            font-size: 1.2rem;
        }

        .mobile-only-trigger {
            display: none;
        }

        /* Desktop nav holds many links; collapse to drawer + bottom bar below 992px */
        @media (max-width: 992px) {
            .mobile-only-trigger { display: inline-block; }
            .bottom-nav { display: block; }
            body { padding-bottom: calc(65px + env(safe-area-inset-bottom)); }
            .desktop-nav-links { display: none !important; }
            .client-navbar { padding: 0.65rem 1rem; }
        }

        /* ── App Mode (Android WebView): customer-only production app chrome ── */
        body.app-mode .desktop-nav-links { display: none !important; }
        body.app-mode .mobile-only-trigger { display: inline-block; }
        body.app-mode .bottom-nav { display: block; }
        body.app-mode { padding-bottom: calc(65px + env(safe-area-inset-bottom)); }
        body.app-mode .site-footer { display: none; }
        body.app-mode #download { display: none; }
        .bottom-nav { padding-bottom: calc(0.6rem + env(safe-area-inset-bottom)); }

        /* Site Footer: white base with the header's palette (red brand, slate text) */
        .site-footer {
            position: relative;
            overflow: hidden;
            background: var(--bg-card);
            color: #475569;
            margin-top: 3rem;
            padding: 0;
            border-top: 1px solid var(--border-light);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
            text-align: left;
            font-size: 1rem;
            font-family: var(--font-body, 'Inter', sans-serif);
        }
        .site-footer a { text-decoration: none; }
        .footer-wave { position: absolute; pointer-events: none; opacity: 0.55; }
        .footer-wave-left { left: 0; bottom: 30%; width: 320px; }
        .footer-wave-right { right: 0; bottom: 0; width: 420px; }
        .footer-inner {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 1.5rem 0;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.45fr 1fr 1.1fr;
            gap: 3.5rem;
            padding-bottom: 3.5rem;
            border-bottom: 1px solid var(--border-light);
        }
        .footer-brand { display: flex; align-items: center; gap: 1.75rem; margin-bottom: 1.5rem; }
        .footer-brand-logo { width: 96px; height: 96px; object-fit: cover; border-radius: 50%; flex-shrink: 0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12); }
        .footer-brand-text { border-left: 2px solid #dc2626; padding-left: 1.5rem; }
        .footer-kicker {
            font-size: 0.8rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }
        .footer-title {
            font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1.15;
            color: var(--text-dark);
            margin: 0;
        }
        .footer-title span { display: block; color: #dc2626; }
        .footer-mission { font-size: 0.95rem; line-height: 1.75; color: var(--text-muted); margin: 0 0 1.5rem; }
        .footer-badges { display: flex; flex-wrap: wrap; gap: 0.6rem; }
        .footer-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 0.95rem;
            border: 1px solid var(--border-light);
            border-radius: 999px;
            background: var(--bg-page);
            font-size: 0.8rem;
            line-height: 1.25;
            color: var(--text-body);
        }
        .footer-badge i { font-size: 1.15rem; color: #dc2626; }
        .footer-heading {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #dc2626;
            margin: 0.4rem 0 1.5rem;
        }
        .footer-heading i { font-size: 1.4rem; }
        .footer-portal { list-style: none; margin: 0; padding: 0; }
        .footer-portal a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-dark);
            font-weight: 600;
            transition: color 0.2s, padding 0.2s;
        }
        .footer-portal li:last-child a { border-bottom: none; }
        .footer-portal a i { color: #dc2626; transition: transform 0.2s; }
        .footer-portal a:hover { color: #dc2626; }
        .footer-portal a:hover i { transform: translateX(4px); }
        .footer-contact { display: flex; flex-direction: column; gap: 1.5rem; }
        .footer-contact-item { display: flex; gap: 1rem; align-items: flex-start; }
        .footer-contact-icon {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .footer-contact-label { font-weight: 700; color: #dc2626; font-size: 0.95rem; }
        .footer-contact-value { color: var(--text-body); font-size: 0.95rem; line-height: 1.55; word-break: break-word; }
        a.footer-contact-value:hover { color: #dc2626; }
        .footer-bottom {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1.5rem;
            padding: 1.75rem 0 2.25rem;
        }
        .footer-social { display: flex; gap: 0.85rem; }
        .footer-social a {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--border-light);
            color: var(--text-body);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .footer-social a:hover { border-color: #dc2626; color: #dc2626; background: #fef2f2; }
        .footer-copy { text-align: center; font-size: 0.9rem; color: var(--text-muted); }
        .footer-copy p { margin: 0; }
        .footer-legal { margin-top: 0.5rem !important; }
        .footer-legal a { color: #dc2626; white-space: nowrap; }
        .footer-legal a:hover { color: #b91c1c; }
        .footer-legal span { color: #cbd5e1; margin: 0 0.75rem; }
        .footer-credit { text-align: right; font-size: 0.78rem; color: var(--text-muted); }

        @media (max-width: 992px) {
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
            .footer-about { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .footer-inner { padding: 3rem 1rem 0; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-brand { gap: 1rem; }
            .footer-brand-logo { width: 72px; height: 72px; }
            .footer-brand-text { padding-left: 1rem; }
            .footer-title { font-size: 1.5rem; }
            .footer-bottom { grid-template-columns: 1fr; justify-items: center; }
            .footer-credit { text-align: center; }
            .footer-legal { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.35rem 1.1rem; }
            .footer-legal span { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body class="{{ $isApp ? 'app-mode' : '' }}">

    @if(config('cctn.maintenance_mode') && !auth('admin')->check())
    {{-- ── Client Maintenance Notice Banner (Red/Black) ── --}}
    <div id="client-maintenance-notice" style="
        background: #111111;
        border-bottom: 1px solid rgba(220,38,38,0.3);
        border-left: 4px solid #dc2626;
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        box-shadow: 0 2px 12px rgba(0,0,0,0.4);
        position: relative;
        overflow: hidden;
    ">
        {{-- subtle red glow behind --}}
        <div style="position:absolute; left:0; top:0; bottom:0; width:120px; background:radial-gradient(ellipse at left, rgba(220,38,38,0.08) 0%, transparent 80%); pointer-events:none;"></div>

        <div style="display:flex; align-items:center; gap:0.85rem; flex-wrap:wrap; position:relative;">
            {{-- Pulsing red dot --}}
            <span style="position:relative; width:10px; height:10px; flex-shrink:0; display:inline-block;">
                <span style="position:absolute; inset:0; border-radius:50%; background:#dc2626; animation:mnPulse 1.5s ease-in-out infinite;"></span>
                <span style="position:absolute; inset:2px; border-radius:50%; background:#ef4444;"></span>
            </span>

            {{-- Icon --}}
            <span style="
                width:34px; height:34px; flex-shrink:0;
                border-radius:8px;
                background: rgba(220,38,38,0.12);
                border: 1px solid rgba(220,38,38,0.25);
                display:flex; align-items:center; justify-content:center;
            ">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </span>

            <div>
                <div style="font-size:0.84rem; font-weight:800; color:#ffffff; line-height:1.2; letter-spacing:-0.01em;">
                    Scheduled Maintenance in Progress
                </div>
                <div style="font-size:0.75rem; color:rgba(255,255,255,0.4); margin-top:0.15rem; line-height:1.4;">
                    Some features may be temporarily unavailable. We're working to restore full service shortly.
                </div>
            </div>
        </div>

        {{-- Dismiss --}}
        <button onclick="
            document.getElementById('client-maintenance-notice').style.display='none';
            sessionStorage.setItem('cctn_notice_dismissed','1');
        " style="
            background: rgba(220,38,38,0.1);
            border: 1px solid rgba(220,38,38,0.3);
            color: #ef4444;
            padding: 0.35rem 0.9rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background 0.15s;
        " onmouseover="this.style.background='rgba(220,38,38,0.22)'"
           onmouseout="this.style.background='rgba(220,38,38,0.1)'">Dismiss</button>
    </div>

    <style>
        @keyframes mnPulse {
            0%,100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(2.4); opacity: 0; }
        }
    </style>
    <script>
        if (sessionStorage.getItem('cctn_notice_dismissed') === '1') {
            document.getElementById('client-maintenance-notice').style.display = 'none';
        }
    </script>
    @endif


    <!-- Header Navbar -->
    <header class="client-header">
        <div class="client-navbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="btn-drawer-close mobile-only-trigger" style="color: var(--text-dark);" onclick="toggleDrawer(true)" aria-label="Open Navigation Drawer">
                    <i class="bi bi-list"></i>
                </button>
                <a href="{{ route('home') }}" class="client-brand">
                    <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo" class="client-brand-img">
                    <div>
                        <span class="client-brand-name">BCTVI</span>
                        <span class="client-brand-sub">Broadband Services</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Nav Links -->
            <div class="desktop-nav-links" style="display: flex; align-items: center; gap: clamp(1.25rem, 2.2vw, 2.25rem);">
                @auth('client')
                    @php
                        $unreadCount = \App\Models\Notification::where('for_admin', false)
                            ->where(function($q){ $q->where('client_id', auth('client')->id())->orWhereNull('client_id'); })
                            ->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('home') }}" class="top-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('client.appointments') }}" class="top-nav-link {{ request()->routeIs('client.appointments*') ? 'active' : '' }}">My Bookings</a>
                    <a href="{{ route('client.billing') }}" class="top-nav-link {{ request()->routeIs('client.billing*') ? 'active' : '' }}">Payments</a>
                    <a href="{{ route('client.notifications') }}" class="top-nav-link {{ request()->routeIs('client.notifications*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                        Notifications
                        @if($unreadCount > 0)
                            <span class="drawer-badge" style="margin-left: 0;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('client.book') }}" class="top-nav-btn-solid">Book Installation</a>
                    <form action="{{ route('logout') }}" method="POST" class="top-nav-logout-form" id="logoutFormDesktop" onsubmit="return false;">
                        @csrf
                        <button type="button" class="top-nav-logout" onclick="showLogoutModal('logoutFormDesktop')">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('home') }}" class="top-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('home') }}#plans" class="top-nav-link">Plans</a>
                    <a href="{{ route('home') }}#download" class="top-nav-link">Mobile App</a>
                    <a href="{{ route('home') }}#support" class="top-nav-link">Support</a>
                    <a href="{{ route('home') }}#about" class="top-nav-link">About</a>
                    <a href="{{ route('login') }}" class="top-nav-btn-outline"><i class="bi bi-person-fill" style="font-size: 1.05rem;"></i> Login</a>
                    <a href="{{ route('register') }}" class="top-nav-btn-solid"><i class="bi bi-rocket-takeoff-fill"></i> Get Started</a>
                @endauth
            </div>
            <div class="landing-header-tools">
                @if(request()->routeIs('home'))
                <time class="landing-live-date" id="landingLiveDate" aria-live="polite"></time>
                @endif
                <button type="button" class="theme-toggle-btn" id="landingThemeToggle" aria-label="Switch to dark mode" aria-pressed="false">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Slide-Out Mobile Navigation Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer(false)">
        <div class="client-drawer" onclick="event.stopPropagation()">
            <div class="drawer-header">
                @auth('client')
                    <div class="drawer-user">
                        <img src="{{ auth('client')->user()->profile_photo ? asset(auth('client')->user()->profile_photo) : asset('assets/images/cctn-logo.png') }}" alt="Avatar" class="drawer-avatar">
                        <div>
                            <div class="drawer-user-name">{{ auth('client')->user()->firstname }} {{ auth('client')->user()->lastname }}</div>
                            <div class="drawer-user-role">Acct: {{ auth('client')->user()->account_number ?? 'Client Account' }}</div>
                        </div>
                    </div>
                @else
                    <div class="drawer-user">
                        <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo" class="drawer-avatar">
                        <div>
                            <div class="drawer-user-name">Welcome Client</div>
                            <div class="drawer-user-role">BCTVI Broadband Portal</div>
                        </div>
                    </div>
                @endauth
                <button class="btn-drawer-close" onclick="toggleDrawer(false)" aria-label="Close Navigation Drawer"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="drawer-menu">
                <a href="{{ route('home') }}" class="drawer-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <span class="drawer-item-icon"><i class="bi bi-house-door"></i></span> Home
                </a>
                <a href="{{ route('home') }}#download" class="drawer-item" onclick="toggleDrawer(false)">
                    <span class="drawer-item-icon"><i class="bi bi-phone"></i></span> Mobile App &amp; APK
                </a>

                @auth('client')
                    <a href="{{ route('client.dashboard') }}" class="drawer-item {{ request()->routeIs('client.dashboard*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-speedometer2"></i></span> Dashboard
                    </a>
                    <a href="{{ route('client.appointments') }}" class="drawer-item {{ request()->routeIs('client.appointments*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-journal-text"></i></span> My Bookings
                    </a>
                    <a href="{{ route('client.billing') }}" class="drawer-item {{ request()->routeIs('client.billing*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-credit-card"></i></span> Payments &amp; Statements
                    </a>
                    <a href="{{ route('client.notifications') }}" class="drawer-item {{ request()->routeIs('client.notifications*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-bell"></i></span> Notifications
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="drawer-badge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <div style="border-top: 1px solid var(--bg-subtle); margin: 0.5rem 0;"></div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;" id="logoutFormDrawer" onsubmit="return false;">
                        @csrf
                        <button type="button" class="drawer-item" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer; color: #ef4444;" onclick="showLogoutModal('logoutFormDrawer')">
                            <span class="drawer-item-icon"><i class="bi bi-box-arrow-right"></i></span> Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="drawer-item">
                        <span class="drawer-item-icon"><i class="bi bi-box-arrow-in-right"></i></span> Login
                    </a>
                    <a href="{{ route('register') }}" class="drawer-item">
                        <span class="drawer-item-icon"><i class="bi bi-person-plus"></i></span> Register
                    </a>
                @endauth
            </div>

            <div class="drawer-footer">
                &copy; {{ date('Y') }} BCTVI Broadband Telecommunications.<br>Customer Support: 0999 998 8209
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="lp-main-wrap">
        @if (session('success_message') || session('error_message') || session('redirect_message') || $errors->any())
            <div class="container" style="max-width: 1200px; margin: 1rem auto 0 auto; padding: 0 1rem;">
                @if (session('success_message'))
                    <div class="alert alert-success fade-in">{{ session('success_message') }}</div>
                @endif
                @if (session('error_message'))
                    <div class="alert alert-danger fade-in">{{ session('error_message') }}</div>
                @endif
                @if (session('redirect_message'))
                    <div class="alert alert-warning fade-in">{{ session('redirect_message') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger fade-in">
                        <ul style="margin:0; padding-left:1rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation Bar for Mobile Viewports -->
    <nav class="bottom-nav">
        <div class="bottom-nav-grid">
            <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <span class="bottom-nav-icon"><i class="bi bi-house-door-fill"></i></span>
                <span>Home</span>
            </a>
            @auth('client')
                <a href="{{ route('client.appointments') }}" class="bottom-nav-item {{ request()->routeIs('client.appointments*') ? 'active' : '' }}">
                    <span class="bottom-nav-icon"><i class="bi bi-journal-text"></i></span>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('client.book') }}" class="bottom-nav-item {{ request()->routeIs('client.book*') ? 'active' : '' }}" style="color:#dc2626;">
                    <span class="bottom-nav-icon" style="background:#dc2626; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-10px; box-shadow:0 4px 10px rgba(220,38,38,0.3);"><i class="bi bi-lightning-charge-fill"></i></span>
                    <span style="margin-top:2px;">Book</span>
                </a>
                <a href="{{ route('client.billing') }}" class="bottom-nav-item {{ request()->routeIs('client.billing*') ? 'active' : '' }}">
                    <span class="bottom-nav-icon"><i class="bi bi-credit-card"></i></span>
                    <span>Payments</span>
                </a>
                <a href="{{ route('client.notifications') }}" class="bottom-nav-item {{ request()->routeIs('client.notifications*') ? 'active' : '' }}">
                    <span class="bottom-nav-icon"><i class="bi bi-bell"></i></span>
                    <span>Alerts</span>
                </a>
            @else
                <a href="{{ route('home') }}#plans" class="bottom-nav-item">
                    <span class="bottom-nav-icon"><i class="bi bi-wifi"></i></span>
                    <span>Plans</span>
                </a>
                <a href="{{ route('register') }}" class="bottom-nav-item" style="color:#dc2626;">
                    <span class="bottom-nav-icon" style="background:#dc2626; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-top:-10px; box-shadow:0 4px 10px rgba(220,38,38,0.3);"><i class="bi bi-person-plus-fill"></i></span>
                    <span>Join</span>
                </a>
                <a href="{{ route('login') }}" class="bottom-nav-item">
                    <span class="bottom-nav-icon"><i class="bi bi-box-arrow-in-right"></i></span>
                    <span>Login</span>
                </a>
            @endauth
        </div>
    </nav>

    <footer class="site-footer">
        <svg class="footer-wave footer-wave-left" viewBox="0 0 320 180" fill="none" aria-hidden="true">
            <path d="M0 40 C 90 60, 160 120, 320 180" stroke="#dc2626" stroke-opacity=".35"/>
            <path d="M0 70 C 100 80, 170 140, 300 180" stroke="#dc2626" stroke-opacity=".25"/>
        </svg>
        <svg class="footer-wave footer-wave-right" viewBox="0 0 420 200" fill="none" aria-hidden="true">
            <path d="M0 200 C 160 180, 280 90, 420 20" stroke="#dc2626" stroke-opacity=".3"/>
            <path d="M40 200 C 190 175, 300 110, 420 60" stroke="#dc2626" stroke-opacity=".22"/>
            <path d="M90 200 C 230 180, 320 130, 420 100" stroke="#dc2626" stroke-opacity=".15"/>
        </svg>

        <div class="footer-inner">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-brand">
                        <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo" class="footer-brand-logo">
                        <div class="footer-brand-text">
                            <div class="footer-kicker">Bantayan Island</div>
                            <h2 class="footer-title">BCTVI Broadband <span>Client Portal</span></h2>
                        </div>
                    </div>
                    <p class="footer-mission">
                        Our mission is to keep Bantayan Island connected with fast, reliable cable TV and
                        internet service, backed by simple online booking, transparent billing, and
                        responsive local support.
                    </p>
                    <div class="footer-badges">
                        <span class="footer-badge"><i class="bi bi-wifi"></i> Reliable<br>Connection</span>
                        <span class="footer-badge"><i class="bi bi-people"></i> Responsive<br>Support</span>
                        <span class="footer-badge"><i class="bi bi-shield-check"></i> Transparent<br>Billing</span>
                    </div>
                </div>

                <div>
                    <h3 class="footer-heading"><i class="bi bi-grid"></i> Portal Access</h3>
                    <ul class="footer-portal">
                        <li><a href="{{ route('login') }}">Customer Sign In <i class="bi bi-arrow-right"></i></a></li>
                        <li><a href="{{ route('register') }}">Customer Registration <i class="bi bi-arrow-right"></i></a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="footer-heading"><i class="bi bi-telephone-inbound"></i> Contact Info</h3>
                    <div class="footer-contact">
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="bi bi-geo-alt-fill"></i></span>
                            <div>
                                <div class="footer-contact-label">Address</div>
                                <div class="footer-contact-value">Bantayan Island, Cebu,<br>Philippines</div>
                            </div>
                        </div>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="bi bi-envelope-fill"></i></span>
                            <div>
                                <div class="footer-contact-label">Email</div>
                                <a href="mailto:bctvibantayanisland@gmail.com" class="footer-contact-value">bctvibantayanisland@gmail.com</a>
                            </div>
                        </div>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="bi bi-telephone-fill"></i></span>
                            <div>
                                <div class="footer-contact-label">Phone</div>
                                <a href="tel:+639999988209" class="footer-contact-value">0999 998 8209</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-social">
                    <a href="https://www.facebook.com/bogocable.bantayan" target="_blank" rel="noopener" aria-label="BCTVI on Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://m.me/bogocable.bantayan" target="_blank" rel="noopener" aria-label="Message BCTVI on Messenger"><i class="bi bi-messenger"></i></a>
                    <a href="mailto:bctvibantayanisland@gmail.com" aria-label="Email BCTVI"><i class="bi bi-envelope"></i></a>
                </div>
                <div class="footer-copy">
                    <p>&copy; {{ date('Y') }} BCTVI Broadband Telecommunications. All Rights Reserved.</p>
                    <p class="footer-legal">
                        <a href="{{ route('terms') }}">Terms &amp; Conditions</a><span>|</span><a href="{{ route('home') }}#plans">Plans</a><span>|</span><a href="{{ route('home') }}#support">Support</a><span>|</span><a href="{{ route('home') }}#about">About</a>
                    </p>
                </div>
                <div class="footer-credit">Developed by Clinton Jay</div>
            </div>
        </div>
    </footer>

    <script>
        function toggleDrawer(open) {
            const drawer = document.getElementById('drawerOverlay');
            if (drawer) {
                if (open) drawer.classList.add('active');
                else drawer.classList.remove('active');
            }
        }
    </script>
    @if(request()->routeIs('home'))
    <script>
        (function () {
            var toggle = document.getElementById('landingThemeToggle');
            var liveDate = document.getElementById('landingLiveDate');
            if (!toggle || !liveDate) return;

            function setTheme(theme) {
                var isDark = theme === 'dark';
                document.documentElement.dataset.theme = theme;
                toggle.setAttribute('aria-pressed', String(isDark));
                toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                try { localStorage.setItem('bctvi-theme', theme); } catch (error) {}
            }

            toggle.addEventListener('click', function () {
                setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
            });
            setTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');

            function updateDate() {
                var now = new Date();
                liveDate.dateTime = now.toISOString();
                liveDate.textContent = new Intl.DateTimeFormat('en-PH', {
                    timeZone: 'Asia/Manila', weekday: 'short', month: 'short', day: 'numeric',
                    year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit'
                }).format(now);
                liveDate.title = 'Current Philippine time';
            }

            updateDate();
            window.setInterval(updateDate, 1000);
        })();
    </script>
    @endif
    @if(request()->routeIs('home'))
    @include('partials.chatbot')
    @endif

    @stack('scripts')

    {{-- Logout Confirmation Modal --}}
    <div id="logoutModal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" style="
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        align-items: center;
        justify-content: center;
    ">
        {{-- Backdrop --}}
        <div id="logoutModalBackdrop" onclick="hideLogoutModal()" style="
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        "></div>

        {{-- Dialog card --}}
        <div style="
            position: relative;
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem 2rem 1.5rem;
            width: min(92vw, 380px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.10);
            animation: logoutModalIn 0.22s cubic-bezier(.34,1.56,.64,1) both;
            text-align: center;
        ">
            {{-- Icon --}}
            <div style="
                width: 56px; height: 56px;
                border-radius: 50%;
                background: #fef2f2;
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 1.1rem;
            ">
                <i class="bi bi-box-arrow-right" style="font-size: 1.6rem; color: #ef4444;"></i>
            </div>

            <h2 id="logoutModalTitle" style="margin: 0 0 0.4rem; font-size: 1.1rem; font-weight: 700; color: #0f172a;">Sign out?</h2>
            <p style="margin: 0 0 1.6rem; font-size: 0.9rem; color: #64748b; line-height: 1.5;">Are you sure you want to log out of your account?</p>

            <div style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="hideLogoutModal()" style="
                    flex: 1;
                    padding: 0.65rem 1rem;
                    border-radius: 8px;
                    border: 1.5px solid #e2e8f0;
                    background: #f8fafc;
                    color: #334155;
                    font-size: 0.9rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.15s;
                " onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'">
                    Cancel
                </button>
                <button type="button" id="logoutConfirmBtn" onclick="submitLogoutForm()" style="
                    flex: 1;
                    padding: 0.65rem 1rem;
                    border-radius: 8px;
                    border: none;
                    background: #ef4444;
                    color: #ffffff;
                    font-size: 0.9rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.15s;
                " onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                    Yes, sign out
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes logoutModalIn {
            from { opacity: 0; transform: scale(0.88) translateY(12px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>

    <script>
        var _logoutActiveFormId = null;

        function showLogoutModal(formId) {
            _logoutActiveFormId = formId;
            var modal = document.getElementById('logoutModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('logoutConfirmBtn').focus();
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = '';
            _logoutActiveFormId = null;
        }

        function submitLogoutForm() {
            if (_logoutActiveFormId) {
                var form = document.getElementById(_logoutActiveFormId);
                if (form) form.submit();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideLogoutModal();
        });
    </script>
    {{-- Always-on: Live Clock + Dark Mode Toggle --}}
    <script>
        (function () {
            var toggle   = document.getElementById('landingThemeToggle');
            var liveDate = document.getElementById('landingLiveDate');

            function setTheme(theme) {
                var isDark = theme === 'dark';
                document.documentElement.dataset.theme = theme;
                if (toggle) {
                    toggle.setAttribute('aria-pressed', String(isDark));
                    toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                }
                try { localStorage.setItem('bctvi-theme', theme); } catch (e) {}
            }

            // Initialise theme from storage or OS preference
            var saved = null;
            try { saved = localStorage.getItem('bctvi-theme'); } catch (e) {}
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            setTheme(saved ? saved : (prefersDark ? 'dark' : 'light'));

            if (toggle) {
                toggle.addEventListener('click', function () {
                    setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
                });
            }

            // Live clock — Philippine time
            if (liveDate) {
                function updateDate() {
                    var now = new Date();
                    liveDate.dateTime = now.toISOString();
                    liveDate.textContent = new Intl.DateTimeFormat('en-PH', {
                        timeZone: 'Asia/Manila',
                        weekday: 'short', month: 'short', day: 'numeric',
                        year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit'
                    }).format(now);
                    liveDate.title = 'Current Philippine time';
                }
                updateDate();
                window.setInterval(updateDate, 1000);
            }
        })();
    </script>
    {{-- Right-click & DevTools Restriction --}}

    <div id="devtools-toast" style="
        display: none;
        position: fixed;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2147483647;
        background: #1e293b;
        color: #f1f5f9;
        padding: 0.75rem 1.4rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-family: Arial, sans-serif;
        box-shadow: 0 8px 30px rgba(0,0,0,0.35);
        white-space: nowrap;
        pointer-events: none;
        border-left: 4px solid #ef4444;
    ">🔒 Right-click and browser developer tools have been restricted on this website.</div>

    <script>
        (function () {
            'use strict';

            /* ── 1. Disable right-click context menu ─────────────────────── */
            document.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                showSecurityToast();
                return false;
            });

            /* ── 2. Block keyboard shortcuts ────────────────────────────── */
            document.addEventListener('keydown', function (e) {
                var blocked = false;

                // F12
                if (e.keyCode === 123) blocked = true;
                // Ctrl+Shift+I (Inspector)
                if (e.ctrlKey && e.shiftKey && e.keyCode === 73) blocked = true;
                // Ctrl+Shift+J (Console)
                if (e.ctrlKey && e.shiftKey && e.keyCode === 74) blocked = true;
                // Ctrl+Shift+C (Element picker)
                if (e.ctrlKey && e.shiftKey && e.keyCode === 67) blocked = true;
                // Ctrl+U (View source)
                if (e.ctrlKey && e.keyCode === 85) blocked = true;
                // Ctrl+S (Save page)
                if (e.ctrlKey && e.keyCode === 83) blocked = true;

                if (blocked) {
                    e.preventDefault();
                    e.stopPropagation();
                    showSecurityToast();
                    return false;
                }
            });

            /* ── 3. DevTools open detection via window size ──────────────── */
            var _devToolsOpen = false;
            var _threshold = 160;

            function checkDevTools() {
                var widthDiff  = window.outerWidth  - window.innerWidth;
                var heightDiff = window.outerHeight - window.innerHeight;
                var isOpen = widthDiff > _threshold || heightDiff > _threshold;

                if (isOpen && !_devToolsOpen) {
                    _devToolsOpen = true;
                    showSecurityToast(true);
                } else if (!isOpen && _devToolsOpen) {
                    _devToolsOpen = false;
                    hideSecurityToast();
                }
            }
            setInterval(checkDevTools, 1000);

            /* ── 4. Toast helper ─────────────────────────────────────────── */
            var _toastTimer = null;
            function showSecurityToast(persist) {
                var toast = document.getElementById('devtools-toast');
                if (!toast) return;
                toast.style.display = 'block';
                clearTimeout(_toastTimer);
                if (!persist) {
                    _toastTimer = setTimeout(function () {
                        toast.style.display = 'none';
                    }, 3500);
                }
            }
            function hideSecurityToast() {
                var toast = document.getElementById('devtools-toast');
                if (toast) toast.style.display = 'none';
            }
        })();
    </script>
</body>
</html>
