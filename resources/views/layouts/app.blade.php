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
    
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Modern Mobile Client Navigation Drawer & Bottom Bar */
        .client-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .client-navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.75rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
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
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            font-size: 1.25rem;
            font-weight: 900;
            color: #dc2626;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        .client-brand-sub {
            font-size: 0.65rem;
            color: #64748b;
            font-weight: 700;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* Desktop Nav Links & Buttons */
        .top-nav-link {
            position: relative;
            color: #334155;
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
            background: #fff;
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
            background: #fff;
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
            background: #ffffff;
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
            color: #94a3b8;
        }
        .btn-drawer-close {
            background: none;
            border: none;
            color: #94a3b8;
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
            color: #334155;
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
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #94a3b8;
            text-align: center;
        }

        /* Bottom Navigation Bar for Mobile */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
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
            color: #64748b;
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
        body.app-mode .admin-only-link { display: none !important; }
        body.app-mode #download { display: none; }
        .bottom-nav { padding-bottom: calc(0.6rem + env(safe-area-inset-bottom)); }
    </style>
    @stack('styles')
</head>
<body class="{{ $isApp ? 'app-mode' : '' }}">

    <!-- Header Navbar -->
    <header class="client-header">
        <div class="client-navbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="btn-drawer-close mobile-only-trigger" style="color: #0f172a;" onclick="toggleDrawer(true)" aria-label="Open Navigation Drawer">
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
            <div class="desktop-nav-links" style="display: flex; align-items: center; gap: 1.75rem;">
                @auth('client')
                    @php
                        $unreadCount = \App\Models\Notification::where('for_admin', false)
                            ->where(function($q){ $q->where('client_id', auth('client')->id())->orWhereNull('client_id'); })
                            ->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('home') }}" class="top-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('client.appointments') }}" class="top-nav-link {{ request()->routeIs('client.appointments*') ? 'active' : '' }}">My Bookings</a>
                    <a href="{{ route('client.billing') }}" class="top-nav-link {{ request()->routeIs('client.billing*') ? 'active' : '' }}">Payments</a>
                    <a href="{{ route('client.payment-methods') }}" class="top-nav-link {{ request()->routeIs('client.payment-methods*') ? 'active' : '' }}">Payment Methods</a>
                    <a href="{{ route('client.notifications') }}" class="top-nav-link {{ request()->routeIs('client.notifications*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                        Notifications
                        @if($unreadCount > 0)
                            <span class="drawer-badge" style="margin-left: 0;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('client.book') }}" class="top-nav-btn-solid">Book Installation</a>
                    <form action="{{ route('logout') }}" method="POST" class="top-nav-logout-form">
                        @csrf
                        <button type="submit" class="top-nav-logout">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('home') }}" class="top-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('home') }}#plans" class="top-nav-link">Plans</a>
                    <a href="{{ route('home') }}#support" class="top-nav-link">Support</a>
                    <a href="{{ route('home') }}#about" class="top-nav-link">About</a>
                    <a href="{{ route('login') }}" class="top-nav-btn-outline">Login</a>
                    <a href="{{ route('register') }}" class="top-nav-btn-solid">Get Started</a>
                @endauth
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
                    <a href="{{ route('client.payment-methods') }}" class="drawer-item {{ request()->routeIs('client.payment-methods*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-wallet2"></i></span> Payment Methods
                    </a>
                    <a href="{{ route('client.notifications') }}" class="drawer-item {{ request()->routeIs('client.notifications*') ? 'active' : '' }}">
                        <span class="drawer-item-icon"><i class="bi bi-bell"></i></span> Notifications
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="drawer-badge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <div style="border-top: 1px solid #f1f5f9; margin: 0.5rem 0;"></div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="drawer-item" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer; color: #ef4444;">
                            <span class="drawer-item-icon"><i class="bi bi-box-arrow-right"></i></span> Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="drawer-item">
                        <span class="drawer-item-icon"><i class="bi bi-box-arrow-in-right"></i></span> Login
                    </a>
                    <a href="{{ route('admin.login') }}" class="drawer-item admin-only-link">
                        <span class="drawer-item-icon"><i class="bi bi-shield-lock"></i></span> Admin Login
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

    <footer class="site-footer" style="background: #0f172a; padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem; margin-top: 3rem;">
        <p>&copy; {{ date('Y') }} BCTVI Broadband Telecommunications. All Rights Reserved.</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #64748b;">Developed by Clinton Jay</p>
        <p class="admin-only-link" style="margin-top: 0.5rem;"><a href="{{ route('admin.login') }}" style="color: #64748b; text-decoration: none; font-weight: 600;">Staff / Admin Login</a></p>
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
    @include('partials.chatbot')

    @stack('scripts')
</body>
</html>
