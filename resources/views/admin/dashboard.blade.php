@extends('layouts.admin')

@section('title', 'Admin Dashboard - BCTVI Bantayan')

@push('styles')
    <style>
        /* ====================================================
           DASHBOARD – Compact No-Scroll Design
           ==================================================== */
        :root {
            --dash-grad-hero:   linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #1a1a2e 100%);
            --dash-grad-red:    linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            --dash-grad-orange: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            --dash-grad-green:  linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            --dash-grad-blue:   linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --dash-grad-purple: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            --dash-glass:       rgba(255,255,255,0.08);
            --dash-glass-bd:    rgba(255,255,255,0.13);
        }

        /* ── Container ─────────────────────────────────────── */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            font-family: 'Inter','Plus Jakarta Sans',system-ui,sans-serif;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ── Hero ───────────────────────────────────────────── */
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            background: var(--dash-grad-hero);
            box-shadow: 0 8px 30px rgba(15,23,42,0.3);
        }
        .dashboard-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 80% 50%, rgba(37,99,235,0.15) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 10% 80%, rgba(220,38,38,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .dashboard-hero-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
        }
        .dashboard-hero-left { position: relative; z-index: 1; }

        .dashboard-eyebrow {
            display: inline-flex; align-items: center; gap: 0.4rem;
            color: rgba(255,255,255,0.65);
            font-size: 0.62rem; font-weight: 800;
            letter-spacing: 0.12em; text-transform: uppercase;
            margin-bottom: 0.3rem;
            padding: 0.2rem 0.6rem;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 999px;
            background: rgba(255,255,255,0.06);
        }
        .dashboard-live-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 3px rgba(74,222,128,0.3);
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%,100% { box-shadow: 0 0 0 3px rgba(74,222,128,0.3); }
            50%      { box-shadow: 0 0 0 6px rgba(74,222,128,0.08); }
        }
        .welcome-header h2 {
            font-size: 1.3rem; font-weight: 800;
            color: #ffffff; margin: 0 0 0.15rem;
            letter-spacing: -0.02em;
        }
        .welcome-header p { color: rgba(255,255,255,0.6); font-size: 0.78rem; margin: 0; }
        .dashboard-hero-meta { margin-top: 0.3rem; color: rgba(255,255,255,0.45); font-size: 0.7rem; font-weight: 600; }

        .dashboard-hero-actions {
            position: relative; z-index: 1;
            display: flex; gap: 0.6rem; flex-shrink: 0; flex-wrap: wrap;
        }
        .dashboard-primary-action {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.55rem 1rem; border-radius: 10px;
            background: #dc2626; color: #fff;
            font-size: 0.8rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 4px 14px rgba(220,38,38,0.4);
            transition: transform .2s, box-shadow .2s, background .2s;
        }
        .dashboard-primary-action:hover { background:#b91c1c; color:#fff; transform:translateY(-1px); }
        .dashboard-secondary-action {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.55rem 1rem; border-radius: 10px;
            background: var(--dash-glass); color: rgba(255,255,255,0.85);
            font-size: 0.8rem; font-weight: 700; text-decoration: none;
            border: 1px solid var(--dash-glass-bd);
            transition: background .2s, transform .2s;
        }
        .dashboard-secondary-action:hover { background:rgba(255,255,255,0.14); color:#fff; transform:translateY(-1px); }

        /* ── KPI Cards ─────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.7rem;
        }
        .stat-card {
            position: relative; overflow: hidden;
            border-radius: 14px;
            padding: 1rem 1.1rem;
            display: flex; align-items: center; gap: 0.85rem;
            text-decoration: none; color: inherit;
            transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
        }
        .stat-card::after {
            content: ''; position: absolute;
            bottom: -16px; right: -16px;
            width: 64px; height: 64px; border-radius: 50%;
            background: rgba(255,255,255,0.1);
            transition: transform .25s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card:hover::after { transform: scale(1.18); }
        .stat-card--bookings { background: var(--dash-grad-red);    box-shadow: 0 6px 18px rgba(220,38,38,.22); }
        .stat-card--pending  { background: var(--dash-grad-orange);  box-shadow: 0 6px 18px rgba(234,88,12,.22); }
        .stat-card--active   { background: var(--dash-grad-green);   box-shadow: 0 6px 18px rgba(22,163,74,.22); }
        .stat-card--inactive { background: var(--dash-grad-blue);    box-shadow: 0 6px 18px rgba(37,99,235,.22); }
        .stat-icon-wrap {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            color: #fff; flex-shrink: 0;
        }
        .stat-meta { display: flex; flex-direction: column; }
        .stat-title { font-size: 0.67rem; font-weight: 700; color: rgba(255,255,255,0.72); text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 0.15rem; }
        .stat-number { font-size: 1.7rem; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.02em; }

        /* ── Analytics row ─────────────────────────────────── */
        .analytics-row {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr;
            gap: 0.7rem;
            align-items: stretch;
        }
        .dash-card {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border-light, #e8e8e8);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            box-shadow: 0 2px 8px rgba(15,23,42,0.04);
            display: flex; flex-direction: column;
        }
        .dash-card-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        .dash-card-title { font-size: 0.85rem; font-weight: 800; color: var(--text-dark); margin: 0; }
        .chart-subtitle { margin: 0.1rem 0 0; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); }
        .chart-table-toggle {
            flex-shrink: 0; padding: 0.25rem 0.55rem;
            border: 1px solid var(--border-light); border-radius: 999px;
            background: var(--bg-card); color: var(--text-muted);
            font-size: 0.65rem; font-weight: 700; font-family: inherit; cursor: pointer;
            transition: color .15s, border-color .15s, background .15s;
        }
        .chart-table-toggle:hover,
        .chart-table-toggle[aria-expanded="true"] { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
        .chart-legend { display:flex; flex-wrap:wrap; gap:0.7rem; margin-bottom:0.5rem; }
        .chart-legend-item { display:inline-flex; align-items:center; gap:0.35rem; font-size:0.7rem; font-weight:600; color:var(--text-muted); }
        .chart-key { width:12px; height:2px; border-radius:2px; flex-shrink:0; }
        .chart-canvas-wrap { position:relative; height:150px; min-width:0; }
        .chart-empty { display:flex; align-items:center; justify-content:center; height:150px; color:var(--text-muted); font-size:0.8rem; font-weight:600; text-align:center; }
        .chart-table { width:100%; border-collapse:collapse; margin-top:0.6rem; font-size:0.75rem; }
        .chart-table th, .chart-table td { padding:0.35rem 0.5rem; text-align:right; border-bottom:1px solid var(--bg-subtle); color:var(--text-body); }
        .chart-table th:first-child, .chart-table td:first-child { text-align:left; }
        .chart-table thead th { font-size:0.65rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--border-light); }
        .chart-table[hidden] { display:none; }
        .chart-sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

        /* ── Revenue strip ─────────────────────────────────── */
        .revenue-strip {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 0.5rem;
            margin-bottom: 0.6rem;
        }
        .revenue-strip-item {
            padding: 0.55rem 0.75rem; border-radius: 10px;
            border: 1px solid var(--border-light);
            background: var(--bg-page, #f8fafc);
        }
        .revenue-strip-label { font-size: 0.62rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.2rem; }
        .revenue-strip-value { font-size: 0.9rem; font-weight:800; color:var(--text-dark); letter-spacing:-0.01em; }
        .revenue-strip-value--green { color:#16a34a; }
        .revenue-change { display:inline-flex; align-items:center; gap:.15rem; font-size:.62rem; font-weight:700; margin-top:.15rem; padding:.1rem .35rem; border-radius:999px; }
        .revenue-change--up   { background:#f0fdf4; color:#15803d; }
        .revenue-change--down { background:#fef2f2; color:#b91c1c; }
        .revenue-change--flat { background:#f1f5f9; color:#64748b; }

        /* ── Bottom 3-col row ──────────────────────────────── */
        .bottom-row {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr;
            gap: 0.7rem;
            align-items: start;
        }

        /* ── Section header inside card ────────────────────── */
        .section-title-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.55rem; }
        .section-title { font-size:0.82rem; font-weight:800; color:var(--text-dark); margin:0; display:flex; align-items:center; gap:0.5rem; }
        .section-title-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .section-link { font-size:0.7rem; font-weight:700; color:#dc2626; text-decoration:none; display:inline-flex; align-items:center; gap:.25rem; transition:gap .2s; }
        .section-link:hover { gap:.4rem; color:#b91c1c; }

        /* ── Mini table ────────────────────────────────────── */
        .mini-table { width:100%; border-collapse:collapse; font-size:0.77rem; }
        .mini-table th {
            padding: 0.35rem 0.6rem;
            background: var(--bg-page, #f8f8f8);
            border-bottom: 1px solid var(--border-light);
            color: var(--text-muted);
            font-size: 0.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; text-align:left;
        }
        .mini-table td { padding:.45rem .6rem; border-bottom:1px solid var(--bg-subtle,#f3f3f3); color:var(--text-body); vertical-align:middle; }
        .mini-table tbody tr:last-child td { border-bottom:none; }
        .mini-table tbody tr:hover td { background:var(--bg-page,#f8fafc); }

        /* ── Badges ────────────────────────────────────────── */
        .badge-pill { display:inline-block; padding:.18rem .55rem; border-radius:999px; font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
        .badge-active    { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
        .badge-inactive  { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .badge-pending   { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .badge-approved  { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .badge-cancelled { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; }

        /* ── Avatar ────────────────────────────────────────── */
        .avatar-initials { width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:.62rem; font-weight:800; color:#fff; flex-shrink:0; }
        .av-1 { background:linear-gradient(135deg,#dc2626,#b91c1c); }
        .av-2 { background:linear-gradient(135deg,#2563eb,#1d4ed8); }
        .av-3 { background:linear-gradient(135deg,#16a34a,#15803d); }
        .av-4 { background:linear-gradient(135deg,#7c3aed,#6d28d9); }
        .av-5 { background:linear-gradient(135deg,#0891b2,#0e7490); }
        .av-6 { background:linear-gradient(135deg,#d97706,#b45309); }
        .av-7 { background:linear-gradient(135deg,#db2777,#be185d); }
        .av-8 { background:linear-gradient(135deg,#0d9488,#0f766e); }

        /* ── Appointment items ─────────────────────────────── */
        .appt-list { display:flex; flex-direction:column; gap:.4rem; }
        .appt-item { display:flex; align-items:center; gap:.65rem; padding:.5rem .7rem; border-radius:10px; background:var(--bg-page,#f8fafc); border:1px solid var(--border-light); transition:background .15s,transform .15s; }
        .appt-item:hover { background:var(--bg-subtle,#f1f5f9); transform:translateX(2px); }
        .appt-date-box { width:36px; height:40px; border-radius:8px; background:var(--dash-grad-red); display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 3px 8px rgba(220,38,38,.22); }
        .appt-date-day { font-size:.85rem; font-weight:800; color:#fff; line-height:1; }
        .appt-date-mon { font-size:.5rem; font-weight:700; color:rgba(255,255,255,.75); text-transform:uppercase; letter-spacing:.04em; }
        .appt-meta { flex:1; min-width:0; }
        .appt-name    { font-size:.75rem; font-weight:700; color:var(--text-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .appt-service { font-size:.66rem; color:var(--text-muted); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* ── Payment items ─────────────────────────────────── */
        .payment-list { display:flex; flex-direction:column; gap:.4rem; }
        .payment-item { display:flex; align-items:center; gap:.65rem; padding:.5rem .7rem; border-radius:10px; background:var(--bg-page,#f8fafc); border:1px solid var(--border-light); transition:background .15s,transform .15s; }
        .payment-item:hover { background:var(--bg-subtle,#f1f5f9); transform:translateX(2px); }
        .payment-icon { width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg,#dcfce7,#bbf7d0); display:flex; align-items:center; justify-content:center; color:#15803d; flex-shrink:0; }
        .payment-meta { flex:1; min-width:0; }
        .payment-name   { font-size:.75rem; font-weight:700; color:var(--text-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .payment-date   { font-size:.66rem; color:var(--text-muted); font-weight:500; }
        .payment-amount { font-size:.82rem; font-weight:800; color:#16a34a; white-space:nowrap; }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width:1280px) {
            .analytics-row { grid-template-columns:1fr 1fr; }
            .analytics-row > .chart-card:first-child { grid-column:1/-1; }
            .bottom-row { grid-template-columns:1fr 1fr; }
            .stats-row  { grid-template-columns:repeat(2,1fr); }
        }
        @media (max-width:900px) {
            .analytics-row,.bottom-row { grid-template-columns:1fr; }
            .analytics-row > .chart-card:first-child { grid-column:auto; }
        }
        @media (max-width:640px) {
            .stats-row { grid-template-columns:1fr; }
            .revenue-strip { grid-template-columns:1fr 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $revenueHasData = array_sum($salesRevenueTrend['values']) > 0;
        $trendHasData   = array_sum($bookingTrend['total']) > 0;
        $planHasData    = array_sum($bookingsByPlan['values']) > 0;

        $revThis  = $stats['revenue_this_month'];
        $revLast  = $stats['revenue_last_month'];
        if ($revLast > 0) {
            $revChangePct = round((($revThis - $revLast) / $revLast) * 100, 1);
            $revChangeDir = $revChangePct >= 0 ? 'up' : 'down';
        } else {
            $revChangePct = null;
            $revChangeDir = 'flat';
        }

        $avClasses = ['av-1','av-2','av-3','av-4','av-5','av-6','av-7','av-8'];

        // Limit to 5 items each for compact fit
        $clientsDisplay      = $recentClients->take(5);
        $appointmentsDisplay = $upcomingAppointments->take(5);
        $paymentsDisplay     = $recentPayments->take(4);
    @endphp

    <div class="dashboard-container">

        {{-- ── Hero ────────────────────────────────────────────────────── --}}
        <div class="dashboard-hero">
            <div class="dashboard-hero-grid"></div>
            <div class="dashboard-hero-left">
                <div class="dashboard-eyebrow">
                    <span class="dashboard-live-dot"></span>
                    Operations Overview
                </div>
                <div class="welcome-header">
                    <h2>Welcome back, {{ str_ireplace('CCTN', 'BCTVI', $admin->fullname ?? 'BCTVI Admin') }}</h2>
                    <p>Monitor bookings, customers, and service activity from one workspace.</p>
                </div>
                <div class="dashboard-hero-meta">{{ date('l, F j, Y') }} &middot; BCTVI Bantayan</div>
            </div>
            <div class="dashboard-hero-actions">
                <a class="dashboard-primary-action" href="{{ route('admin.appointments') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Manage Bookings
                </a>
                <a class="dashboard-secondary-action" href="{{ route('admin.clients') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Clients
                </a>
            </div>
        </div>

        @if (session('success_message'))
            <div style="background:#dcfce7;color:#15803d;padding:.7rem 1rem;border-radius:10px;font-size:.85rem;font-weight:600;border:1px solid #bbf7d0;">
                ✓ {{ session('success_message') }}
            </div>
        @endif

        {{-- ── KPI Cards ──────────────────────────────────────────────── --}}
        <div class="stats-row">
            <a href="{{ route('admin.appointments') }}" class="stat-card stat-card--bookings">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Total Bookings</span>
                    <span class="stat-number">{{ $stats['total'] }}</span>
                </div>
            </a>
            <a href="{{ route('admin.appointments', ['status' => 'pending']) }}" class="stat-card stat-card--pending">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Pending Approvals</span>
                    <span class="stat-number">{{ $stats['pending'] }}</span>
                </div>
            </a>
            <a href="{{ route('admin.clients', ['filter' => 'active']) }}" class="stat-card stat-card--active">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Active Subscribers</span>
                    <span class="stat-number">{{ $stats['active_clients'] }}</span>
                </div>
            </a>
            <a href="{{ route('admin.clients', ['filter' => 'inactive']) }}" class="stat-card stat-card--inactive">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Inactive Subscribers</span>
                    <span class="stat-number">{{ $stats['inactive_clients'] }}</span>
                </div>
            </a>
        </div>

        {{-- ── Charts Row ─────────────────────────────────────────────── --}}
        <div class="analytics-row">

            {{-- Sales Revenue --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Sales Revenue</h3>
                        <p class="chart-subtitle">Collected payments &middot; last 6 months</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="sales-revenue-table" aria-expanded="false" aria-controls="sales-revenue-table">Table</button>
                </div>
                <div class="revenue-strip">
                    <div class="revenue-strip-item">
                        <div class="revenue-strip-label">This Month</div>
                        <div class="revenue-strip-value revenue-strip-value--green">&#8369;{{ number_format($stats['revenue_this_month'], 0) }}</div>
                        @if ($revChangePct !== null)
                            <div class="revenue-change revenue-change--{{ $revChangeDir }}">
                                @if ($revChangeDir === 'up')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>+{{ $revChangePct }}%
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>{{ $revChangePct }}%
                                @endif
                                vs last month
                            </div>
                        @endif
                    </div>
                    <div class="revenue-strip-item">
                        <div class="revenue-strip-label">Last Month</div>
                        <div class="revenue-strip-value">&#8369;{{ number_format($stats['revenue_last_month'], 0) }}</div>
                    </div>
                    <div class="revenue-strip-item">
                        <div class="revenue-strip-label">All-Time</div>
                        <div class="revenue-strip-value">&#8369;{{ number_format($stats['total_revenue'], 0) }}</div>
                    </div>
                </div>
                @if ($revenueHasData)
                    <div class="chart-legend">
                        <span class="chart-legend-item"><span class="chart-key" style="background:#16a34a;"></span> Collected revenue</span>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="salesRevenueChart" role="img" aria-label="Line chart of collected sales revenue."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No sales revenue in the last 6 months yet.</div>
                @endif
                <table class="chart-table" id="sales-revenue-table" hidden>
                    <caption class="chart-sr-only">Revenue per month</caption>
                    <thead><tr><th scope="col">Month</th><th scope="col">Revenue</th></tr></thead>
                    <tbody>
                        @foreach ($salesRevenueTrend['labels'] as $i => $label)
                            <tr><th scope="row" style="font-weight:600;">{{ $label }}</th><td>&#8369;{{ number_format($salesRevenueTrend['values'][$i], 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Booking Trend --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Booking Trend</h3>
                        <p class="chart-subtitle">Requests per month &middot; last 6 months</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="booking-trend-table" aria-expanded="false" aria-controls="booking-trend-table">Table</button>
                </div>
                @if ($trendHasData)
                    <div class="chart-legend">
                        <span class="chart-legend-item"><span class="chart-key" style="background:#2563eb;"></span> Total</span>
                        <span class="chart-legend-item"><span class="chart-key" style="background:#fb923c;"></span> Approved</span>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="bookingTrendChart" role="img" aria-label="Bar chart of bookings per month."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No bookings in the last 6 months yet.</div>
                @endif
                <table class="chart-table" id="booking-trend-table" hidden>
                    <caption class="chart-sr-only">Bookings per month</caption>
                    <thead><tr><th>Month</th><th>Total</th><th>Approved</th></tr></thead>
                    <tbody>
                        @foreach ($bookingTrend['labels'] as $i => $label)
                            <tr><th scope="row" style="font-weight:600;">{{ $label }}</th><td>{{ $bookingTrend['total'][$i] }}</td><td>{{ $bookingTrend['approved'][$i] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bookings by Plan --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Bookings by Plan</h3>
                        <p class="chart-subtitle">Total requests per internet plan</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="bookings-plan-table" aria-expanded="false" aria-controls="bookings-plan-table">Table</button>
                </div>
                @if ($planHasData)
                    <div class="chart-canvas-wrap">
                        <canvas id="bookingsByPlanChart" role="img" aria-label="Doughnut chart of bookings per plan."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No plan bookings to chart yet.</div>
                @endif
                <table class="chart-table" id="bookings-plan-table" hidden>
                    <caption class="chart-sr-only">Bookings per plan</caption>
                    <thead><tr><th>Plan</th><th>Bookings</th></tr></thead>
                    <tbody>
                        @foreach ($bookingsByPlan['labels'] as $i => $label)
                            <tr><th scope="row" style="font-weight:600;">{{ $label }}</th><td>{{ $bookingsByPlan['values'][$i] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Bottom 3-col Row ──────────────────────────────────────── --}}
        <div class="bottom-row">

            {{-- Client Database --}}
            <div class="dash-card">
                <div class="section-title-row">
                    <h3 class="section-title">
                        <span class="section-title-dot" style="background:#2563eb;"></span>
                        Client Database
                    </h3>
                    <a href="{{ route('admin.clients') }}" class="section-link">
                        View all <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                @if ($clientsDisplay->isNotEmpty())
                    <div style="overflow-x:auto;">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientsDisplay as $idx => $client)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.5rem;">
                                                <span class="avatar-initials {{ $avClasses[$idx % count($avClasses)] }}">
                                                    {{ strtoupper(substr($client->firstname ?? '?',0,1)) }}{{ strtoupper(substr($client->lastname ?? '',0,1)) }}
                                                </span>
                                                <div>
                                                    <div style="font-weight:700;font-size:.76rem;color:var(--text-dark);white-space:nowrap;">{{ $client->firstname }} {{ $client->lastname }}</div>
                                                    <div style="font-size:.65rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">{{ $client->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:.72rem;font-weight:600;color:var(--text-body);max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $client->currentService?->service_name ? str_ireplace('CCTN','BCTVI',$client->currentService->service_name) : '—' }}
                                        </td>
                                        <td>
                                            @if (($client->account_status ?? '') === 'Active' && ($client->subscription_status ?? '') === 'active')
                                                <span class="badge-pill badge-active">Active</span>
                                            @else
                                                <span class="badge-pill badge-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.68rem;color:var(--text-muted);white-space:nowrap;">{{ $client->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.82rem;font-weight:600;">No clients registered yet.</div>
                @endif
            </div>

            {{-- Client Appointments --}}
            <div class="dash-card">
                <div class="section-title-row">
                    <h3 class="section-title">
                        <span class="section-title-dot" style="background:#dc2626;"></span>
                        Client Appointments
                    </h3>
                    <a href="{{ route('admin.appointments') }}" class="section-link">
                        View all <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                @if ($appointmentsDisplay->isNotEmpty())
                    <div class="appt-list">
                        @foreach ($appointmentsDisplay as $appt)
                            <div class="appt-item">
                                <div class="appt-date-box">
                                    <span class="appt-date-day">{{ $appt->preferred_date?->format('d') ?? '—' }}</span>
                                    <span class="appt-date-mon">{{ $appt->preferred_date?->format('M') ?? '' }}</span>
                                </div>
                                <div class="appt-meta">
                                    <div class="appt-name">{{ $appt->client?->firstname }} {{ $appt->client?->lastname }}</div>
                                    <div class="appt-service">{{ $appt->service ? str_ireplace('CCTN','BCTVI',$appt->service->service_name) : 'No service' }}</div>
                                </div>
                                <span class="badge-pill badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.82rem;font-weight:600;">No upcoming appointments.</div>
                @endif
            </div>

            {{-- Recent Revenue --}}
            <div class="dash-card">
                <div class="section-title-row">
                    <h3 class="section-title">
                        <span class="section-title-dot" style="background:#16a34a;"></span>
                        Recent Revenue
                    </h3>
                    <a href="{{ route('admin.sales') }}" class="section-link">
                        View all <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                @if ($paymentsDisplay->isNotEmpty())
                    <div class="payment-list">
                        @foreach ($paymentsDisplay as $payment)
                            <div class="payment-item">
                                <div class="payment-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </div>
                                <div class="payment-meta">
                                    <div class="payment-name">
                                        {{ $payment->client?->firstname }} {{ $payment->client?->lastname }}
                                        @if(!$payment->client) <span style="color:var(--text-muted);">{{ $payment->account_number }}</span> @endif
                                    </div>
                                    <div class="payment-date">{{ $payment->payment_date?->format('M d, Y') ?? '—' }} &middot; {{ ucfirst($payment->payment_method ?? 'Cash') }}</div>
                                </div>
                                <div class="payment-amount">&#8369;{{ number_format($payment->amount_paid, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.82rem;font-weight:600;">No payments recorded yet.</div>
                @endif
            </div>

        </div>{{-- /bottom-row --}}
    </div>{{-- /dashboard-container --}}
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            document.querySelectorAll('[data-chart-table]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var table = document.getElementById(btn.dataset.chartTable);
                    if (!table) return;
                    var opening = table.hasAttribute('hidden');
                    opening ? table.removeAttribute('hidden') : table.setAttribute('hidden', '');
                    btn.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    btn.textContent = opening ? 'Hide' : 'Table';
                });
            });

            if (typeof Chart === 'undefined') return;

            var SURFACE   = '#ffffff';
            var INK_MUTED = '#94a3b8';
            var INK_SEC   = '#52514e';
            var GRID      = '#f1f5f9';
            var AXIS      = '#e2e8f0';
            var FONT      = '"Inter","Plus Jakarta Sans",system-ui,sans-serif';
            var PLAN_CLR  = ['#2563eb','#fb923c','#16a34a','#7c3aed','#eab308','#0891b2','#64748b','#ec4899'];

            Chart.defaults.font.family = FONT;
            Chart.defaults.font.size   = 10;
            Chart.defaults.color       = INK_MUTED;

            var TT = {
                backgroundColor: '#0f172a', titleColor: '#f8fafc', bodyColor: '#cbd5e1',
                padding: 10, cornerRadius: 8, boxWidth: 7, boxHeight: 7, boxPadding: 3,
                usePointStyle: true,
                titleFont: { family: FONT, size: 11, weight: '700' },
                bodyFont:  { family: FONT, size: 11, weight: '500' }
            };

            // Revenue line chart
            var rc = document.getElementById('salesRevenueChart');
            if (rc) {
                var rev = @json($salesRevenueTrend);
                new Chart(rc, {
                    type: 'line',
                    data: {
                        labels: rev.labels,
                        datasets: [{
                            label: 'Collected revenue', data: rev.values,
                            borderColor: '#16a34a',
                            backgroundColor: function(ctx) {
                                var g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 150);
                                g.addColorStop(0, 'rgba(22,163,74,0.2)');
                                g.addColorStop(1, 'rgba(22,163,74,0.01)');
                                return g;
                            },
                            fill: true, borderWidth: 2, tension: 0.38,
                            pointRadius: 3, pointHoverRadius: 5,
                            pointBackgroundColor: '#16a34a', pointBorderColor: SURFACE, pointBorderWidth: 2, pointHitRadius: 20
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        layout: { padding: { top: 4, right: 6 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: Object.assign({}, TT, { callbacks: { label: function(c) { return ' \u20b1' + Number(c.parsed.y).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); } } })
                        },
                        elements: { line: { borderCapStyle: 'round', borderJoinStyle: 'round' } },
                        scales: {
                            x: { grid:{display:false}, border:{color:AXIS}, ticks:{color:INK_MUTED,padding:4,font:{size:10,weight:'600'}} },
                            y: { beginAtZero:true, grid:{color:GRID,drawTicks:false}, border:{display:false},
                                 ticks:{color:INK_MUTED,padding:6,maxTicksLimit:5,callback:function(v){return '\u20b1'+Number(v).toLocaleString();},font:{size:10}} }
                        }
                    }
                });
            }

            // Booking trend bar chart
            var tc = document.getElementById('bookingTrendChart');
            if (tc) {
                var trend = @json($bookingTrend);
                new Chart(tc, {
                    type: 'bar',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            { label:'Total bookings', data:trend.total,    backgroundColor:'#2563eb', borderRadius:5, borderSkipped:false, maxBarThickness:14 },
                            { label:'Approved',       data:trend.approved, backgroundColor:'#fb923c', borderRadius:5, borderSkipped:false, maxBarThickness:14 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        layout: { padding: { top: 6 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: TT },
                        scales: {
                            x: { grid:{display:false}, border:{color:AXIS}, ticks:{color:INK_MUTED,padding:6,maxRotation:40,minRotation:0,autoSkip:false,font:{size:9,weight:'600'}} },
                            y: { beginAtZero:true, grid:{color:GRID,drawTicks:false}, border:{display:false},
                                 ticks:{color:INK_MUTED,precision:0,padding:6,maxTicksLimit:5,font:{size:10}} }
                        }
                    }
                });
            }

            // Bookings by plan doughnut
            var pc = document.getElementById('bookingsByPlanChart');
            if (pc) {
                var plans = @json($bookingsByPlan);
                var pClr  = plans.labels.map(function(_,i){ return PLAN_CLR[i % PLAN_CLR.length]; });
                new Chart(pc, {
                    type: 'doughnut',
                    data: {
                        labels: plans.labels,
                        datasets: [{ label:'Bookings', data:plans.values, backgroundColor:pClr, hoverBackgroundColor:pClr, borderColor:SURFACE, borderWidth:3, hoverOffset:6 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '62%', layout: { padding: 2 },
                        plugins: {
                            legend: { display:true, position:'right', labels:{color:INK_SEC,usePointStyle:true,pointStyle:'circle',boxWidth:7,boxHeight:7,padding:10,font:{size:10,weight:'600'}} },
                            tooltip: Object.assign({}, TT, { callbacks: { label: function(c) { var n=c.parsed; var t=c.dataset.data.reduce(function(s,v){return s+Number(v);},0); var p=t?Math.round((n/t)*100):0; return ' '+c.label+': '+n+(n===1?' booking':' bookings')+' ('+p+'%)'; } } })
                        }
                    }
                });
            }
        })();
    </script>
@endpush
