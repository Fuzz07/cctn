@extends('layouts.admin')

@section('title', 'Admin Dashboard - BCTVI Bantayan')

@push('styles')
    <style>
        /* =====================================================
           DASHBOARD – Modern Grading Design System
           ===================================================== */

        /* CSS custom property overrides for the graded palette */
        :root {
            --dash-grad-hero:    linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1a1a2e 100%);
            --dash-grad-red:     linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            --dash-grad-orange:  linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            --dash-grad-green:   linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            --dash-grad-blue:    linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --dash-grad-purple:  linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            --dash-grad-cyan:    linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            --dash-glass:        rgba(255,255,255,0.07);
            --dash-glass-border: rgba(255,255,255,0.12);
        }

        /* ── Page wrapper ─────────────────────────────────── */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: 'Inter', 'Plus Jakarta Sans', system-ui, sans-serif;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ── Hero banner ──────────────────────────────────── */
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 2rem 2rem;
            border-radius: 20px;
            background: var(--dash-grad-hero);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.35);
        }

        .dashboard-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 80% 50%, rgba(37,99,235,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 10% 80%, rgba(220,38,38,0.12) 0%, transparent 50%);
            pointer-events: none;
        }

        .dashboard-hero-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .dashboard-hero-left { position: relative; z-index: 1; }

        .dashboard-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            padding: 0.3rem 0.75rem;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 999px;
            backdrop-filter: blur(4px);
            background: rgba(255,255,255,0.07);
        }

        .dashboard-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.3);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.1); }
        }

        .welcome-header h2 {
            font-size: 1.85rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 0.3rem 0;
            letter-spacing: -0.02em;
        }

        .welcome-header p {
            color: rgba(255,255,255,0.65);
            font-size: 0.9rem;
            margin: 0;
        }

        .dashboard-hero-meta {
            margin-top: 0.75rem;
            color: rgba(255,255,255,0.55);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .dashboard-hero-actions {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .dashboard-primary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            background: #dc2626;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .dashboard-primary-action:hover {
            background: #b91c1c;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(220, 38, 38, 0.5);
        }

        .dashboard-secondary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            background: var(--dash-glass);
            color: rgba(255,255,255,0.85);
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid var(--dash-glass-border);
            transition: background 0.2s, transform 0.2s;
        }

        .dashboard-secondary-action:hover {
            background: rgba(255,255,255,0.13);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ── KPI Stat Cards ───────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 1.5rem 1.5rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; right: 0;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            transform: translate(25%, 25%);
            transition: transform 0.25s;
        }

        .stat-card:hover { transform: translateY(-4px); }
        .stat-card:hover::after { transform: translate(20%, 20%) scale(1.1); }

        .stat-card--bookings {
            background: var(--dash-grad-red);
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.25);
        }
        .stat-card--pending {
            background: var(--dash-grad-orange);
            box-shadow: 0 8px 24px rgba(234, 88, 12, 0.25);
        }
        .stat-card--active {
            background: var(--dash-grad-green);
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.25);
        }
        .stat-card--inactive {
            background: var(--dash-grad-blue);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25);
        }
        .stat-card--revenue {
            background: var(--dash-grad-purple);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }

        .stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.75);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .stat-sub {
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
        }

        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.95);
            width: fit-content;
        }

        /* ── Section headers ──────────────────────────────── */
        .section-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .section-title-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .section-link {
            font-size: 0.78rem;
            font-weight: 700;
            color: #dc2626;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: gap 0.2s;
        }

        .section-link:hover { gap: 0.5rem; color: #b91c1c; }

        /* ── Analytics layout: 3 charts ──────────────────── */
        .analytics-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 1rem;
            align-items: stretch;
        }

        /* ── Generic dash card ────────────────────────────── */
        .dash-card {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border-light, #e8e8e8);
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
        }

        .chart-card { min-width: 0; }

        .chart-subtitle {
            margin: 0.2rem 0 0 0;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .chart-table-toggle {
            flex-shrink: 0;
            padding: 0.35rem 0.7rem;
            border: 1px solid var(--border-light);
            border-radius: 999px;
            background: var(--bg-card);
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: color 0.15s, border-color 0.15s, background 0.15s;
        }

        .chart-table-toggle:hover,
        .chart-table-toggle[aria-expanded="true"] {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .chart-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .chart-key {
            width: 14px;
            height: 3px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .chart-canvas-wrap {
            position: relative;
            height: 230px;
            min-width: 0;
        }

        .chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 230px;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.8rem;
            font-variant-numeric: tabular-nums;
        }

        .chart-table th,
        .chart-table td {
            padding: 0.45rem 0.6rem;
            text-align: right;
            border-bottom: 1px solid var(--bg-subtle);
            color: var(--text-body);
        }

        .chart-table th:first-child,
        .chart-table td:first-child { text-align: left; }

        .chart-table thead th {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-light);
        }

        .chart-table[hidden] { display: none; }

        .chart-sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        .dash-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .dash-card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
        }

        /* ── Bottom two-column layout ─────────────────────── */
        .bottom-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* ── Client database mini-table ───────────────────── */
        .mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        .mini-table th {
            padding: 0.5rem 0.75rem;
            background: var(--bg-page, #f8f8f8);
            border-bottom: 1px solid var(--border-light);
            color: var(--text-muted);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }

        .mini-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--bg-subtle, #f3f3f3);
            color: var(--text-body);
            vertical-align: middle;
        }

        .mini-table tbody tr:last-child td { border-bottom: none; }
        .mini-table tbody tr:hover td { background: var(--bg-page, #f8fafc); }

        /* ── Status badges ────────────────────────────────── */
        .badge-pill {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .badge-active    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-inactive  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-pending   { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-approved  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-cancelled { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* ── Avatar initials ──────────────────────────────── */
        .avatar-initials {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            letter-spacing: 0.02em;
        }

        /* ── Revenue payment list ─────────────────────────── */
        .payment-list { display: flex; flex-direction: column; gap: 0.6rem; }

        .payment-item {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            background: var(--bg-page, #f8fafc);
            border: 1px solid var(--border-light);
            transition: background 0.15s, transform 0.15s;
        }

        .payment-item:hover {
            background: var(--bg-subtle, #f1f5f9);
            transform: translateX(3px);
        }

        .payment-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #15803d;
            flex-shrink: 0;
        }

        .payment-meta { flex: 1; min-width: 0; }
        .payment-name { font-size: 0.82rem; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .payment-date { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; }
        .payment-amount { font-size: 0.9rem; font-weight: 800; color: #16a34a; white-space: nowrap; }

        /* ── Appointment list ─────────────────────────────── */
        .appt-list { display: flex; flex-direction: column; gap: 0.6rem; }

        .appt-item {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            background: var(--bg-page, #f8fafc);
            border: 1px solid var(--border-light);
            transition: background 0.15s, transform 0.15s;
        }

        .appt-item:hover {
            background: var(--bg-subtle, #f1f5f9);
            transform: translateX(3px);
        }

        .appt-date-box {
            width: 44px;
            height: 48px;
            border-radius: 10px;
            background: var(--dash-grad-red);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(220,38,38,0.25);
        }

        .appt-date-day { font-size: 1rem; font-weight: 800; color: #fff; line-height: 1; }
        .appt-date-mon { font-size: 0.58rem; font-weight: 700; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.05em; }
        .appt-meta { flex: 1; min-width: 0; }
        .appt-name { font-size: 0.82rem; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .appt-service { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ── Revenue summary strip ────────────────────────── */
        .revenue-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .revenue-strip-item {
            padding: 1rem 1.1rem;
            border-radius: 14px;
            border: 1px solid var(--border-light);
            background: var(--bg-page, #f8fafc);
        }

        .revenue-strip-label {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.35rem;
        }

        .revenue-strip-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .revenue-strip-value--green { color: #16a34a; }

        .revenue-change {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            font-size: 0.7rem;
            font-weight: 700;
            margin-top: 0.2rem;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
        }

        .revenue-change--up   { background: #f0fdf4; color: #15803d; }
        .revenue-change--down { background: #fef2f2; color: #b91c1c; }
        .revenue-change--flat { background: #f1f5f9; color: #64748b; }

        /* ── Avatar colour palette ────────────────────────── */
        .av-1  { background: linear-gradient(135deg,#dc2626,#b91c1c); }
        .av-2  { background: linear-gradient(135deg,#2563eb,#1d4ed8); }
        .av-3  { background: linear-gradient(135deg,#16a34a,#15803d); }
        .av-4  { background: linear-gradient(135deg,#7c3aed,#6d28d9); }
        .av-5  { background: linear-gradient(135deg,#0891b2,#0e7490); }
        .av-6  { background: linear-gradient(135deg,#d97706,#b45309); }
        .av-7  { background: linear-gradient(135deg,#db2777,#be185d); }
        .av-8  { background: linear-gradient(135deg,#0d9488,#0f766e); }

        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 1280px) {
            .analytics-row { grid-template-columns: 1fr 1fr; }
            .analytics-row > .chart-card:first-child { grid-column: 1 / -1; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .bottom-row { grid-template-columns: 1fr; }
            .analytics-row { grid-template-columns: 1fr; }
            .analytics-row > .chart-card:first-child { grid-column: auto; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .revenue-strip { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .stats-row { grid-template-columns: 1fr; }
            .revenue-strip { grid-template-columns: 1fr; }
            .welcome-header h2 { font-size: 1.4rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $revenueHasData = array_sum($salesRevenueTrend['values']) > 0;
        $trendHasData   = array_sum($bookingTrend['total']) > 0;
        $planHasData    = array_sum($bookingsByPlan['values']) > 0;

        // Revenue change vs last month
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
    @endphp

    <div class="dashboard-container">

        {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Manage Bookings
                </a>
                <a class="dashboard-secondary-action" href="{{ route('admin.clients') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Clients
                </a>
            </div>
        </div>

        @if (session('success_message'))
            <div style="background:#dcfce7; color:#15803d; padding: 1rem 1.25rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600; border: 1px solid #bbf7d0;">
                ✓ {{ session('success_message') }}
            </div>
        @endif

        {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
        <div class="stats-row">
            {{-- Total Bookings --}}
            <a href="{{ route('admin.appointments') }}" class="stat-card stat-card--bookings">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <div class="stat-title">Total Bookings</div>
                    <div class="stat-number">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    All time
                </div>
            </a>

            {{-- Pending Approvals --}}
            <a href="{{ route('admin.appointments', ['status' => 'pending']) }}" class="stat-card stat-card--pending">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="stat-title">Pending Approvals</div>
                    <div class="stat-number">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-badge">Awaiting review</div>
            </a>

            {{-- Active Subscribers --}}
            <a href="{{ route('admin.clients', ['filter' => 'active']) }}" class="stat-card stat-card--active">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-title">Active Subscribers</div>
                    <div class="stat-number">{{ $stats['active_clients'] }}</div>
                </div>
                <div class="stat-badge">Subscribed</div>
            </a>

            {{-- Inactive Subscribers --}}
            <a href="{{ route('admin.clients', ['filter' => 'inactive']) }}" class="stat-card stat-card--inactive">
                <div class="stat-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>
                </div>
                <div>
                    <div class="stat-title">Inactive Subscribers</div>
                    <div class="stat-number">{{ $stats['inactive_clients'] }}</div>
                </div>
                <div class="stat-badge">Unsubscribed</div>
            </a>
        </div>

        {{-- ── Analytics Charts ────────────────────────────────────────────────── --}}
        <div class="analytics-row">

            {{-- Sales Revenue Line Chart --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Sales Revenue</h3>
                        <p class="chart-subtitle">Collected payments &middot; last 6 months</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="sales-revenue-table" aria-expanded="false" aria-controls="sales-revenue-table">Table view</button>
                </div>

                {{-- Revenue summary strip --}}
                <div class="revenue-strip">
                    <div class="revenue-strip-item">
                        <div class="revenue-strip-label">This Month</div>
                        <div class="revenue-strip-value revenue-strip-value--green">&#8369;{{ number_format($stats['revenue_this_month'], 0) }}</div>
                        @if ($revChangePct !== null)
                            <div class="revenue-change revenue-change--{{ $revChangeDir }}">
                                @if ($revChangeDir === 'up')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                                    +{{ $revChangePct }}%
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
                                    {{ $revChangePct }}%
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
                        <div class="revenue-strip-label">All-Time Total</div>
                        <div class="revenue-strip-value">&#8369;{{ number_format($stats['total_revenue'], 0) }}</div>
                    </div>
                </div>

                @if ($revenueHasData)
                    <div class="chart-legend">
                        <span class="chart-legend-item">
                            <span class="chart-key" style="background:#16a34a;"></span> Collected revenue
                        </span>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="salesRevenueChart" role="img" aria-label="Line chart of collected sales revenue per month for the last six months."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No sales revenue recorded in the last 6 months yet.</div>
                @endif

                <table class="chart-table" id="sales-revenue-table" hidden>
                    <caption class="chart-sr-only">Collected sales revenue per month, last 6 months</caption>
                    <thead><tr><th scope="col">Month</th><th scope="col">Revenue</th></tr></thead>
                    <tbody>
                        @foreach ($salesRevenueTrend['labels'] as $i => $label)
                            <tr>
                                <th scope="row" style="font-weight:600;">{{ $label }}</th>
                                <td>&#8369;{{ number_format($salesRevenueTrend['values'][$i], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Booking Trend Bar Chart --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Booking Trend</h3>
                        <p class="chart-subtitle">Requests per month &middot; last 6 months</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="booking-trend-table" aria-expanded="false" aria-controls="booking-trend-table">Table view</button>
                </div>

                @if ($trendHasData)
                    <div class="chart-legend">
                        <span class="chart-legend-item"><span class="chart-key" style="background:#2a78d6;"></span> Total</span>
                        <span class="chart-legend-item"><span class="chart-key" style="background:#eb6834;"></span> Approved</span>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="bookingTrendChart" role="img" aria-label="Bar chart of total and approved bookings per month."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No bookings recorded in the last 6 months yet.</div>
                @endif

                <table class="chart-table" id="booking-trend-table" hidden>
                    <caption class="chart-sr-only">Bookings per month, last 6 months</caption>
                    <thead><tr><th scope="col">Month</th><th scope="col">Total</th><th scope="col">Approved</th></tr></thead>
                    <tbody>
                        @foreach ($bookingTrend['labels'] as $i => $label)
                            <tr>
                                <th scope="row" style="font-weight:600;">{{ $label }}</th>
                                <td>{{ $bookingTrend['total'][$i] }}</td>
                                <td>{{ $bookingTrend['approved'][$i] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bookings by Plan Doughnut --}}
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Bookings by Plan</h3>
                        <p class="chart-subtitle">Total requests per internet plan</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="bookings-plan-table" aria-expanded="false" aria-controls="bookings-plan-table">Table view</button>
                </div>

                @if ($planHasData)
                    <div class="chart-canvas-wrap">
                        <canvas id="bookingsByPlanChart" role="img" aria-label="Doughnut chart of total bookings per internet plan."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No plan bookings to chart yet.</div>
                @endif

                <table class="chart-table" id="bookings-plan-table" hidden>
                    <caption class="chart-sr-only">Bookings per plan</caption>
                    <thead><tr><th scope="col">Plan</th><th scope="col">Bookings</th></tr></thead>
                    <tbody>
                        @foreach ($bookingsByPlan['labels'] as $i => $label)
                            <tr>
                                <th scope="row" style="font-weight:600;">{{ $label }}</th>
                                <td>{{ $bookingsByPlan['values'][$i] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Bottom Row: Client Database + Appointments + Revenue ────────────── --}}
        <div class="bottom-row">

            {{-- Client Database --}}
            <div class="dash-card">
                <div class="section-title-row">
                    <h3 class="section-title">
                        <span class="section-title-dot" style="background:var(--dash-grad-blue);background:#2563eb;"></span>
                        Client Database
                    </h3>
                    <a href="{{ route('admin.clients') }}" class="section-link">
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                @if ($recentClients->isNotEmpty())
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
                                @foreach ($recentClients as $idx => $client)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                                <span class="avatar-initials {{ $avClasses[$idx % count($avClasses)] }}">
                                                    {{ strtoupper(substr($client->firstname ?? '?', 0, 1)) }}{{ strtoupper(substr($client->lastname ?? '', 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div style="font-weight:700;font-size:0.82rem;color:var(--text-dark);">
                                                        {{ $client->firstname }} {{ $client->lastname }}
                                                    </div>
                                                    <div style="font-size:0.7rem;color:var(--text-muted);">{{ $client->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-size:0.78rem;font-weight:600;color:var(--text-body);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $client->currentService?->service_name ? str_ireplace('CCTN','BCTVI',$client->currentService->service_name) : '—' }}
                                        </td>
                                        <td>
                                            @if ($client->account_status === 'Active' && ($client->subscription_status ?? '') === 'active')
                                                <span class="badge-pill badge-active">Active</span>
                                            @else
                                                <span class="badge-pill badge-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="font-size:0.74rem;color:var(--text-muted);white-space:nowrap;">
                                            {{ $client->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:0.85rem;font-weight:600;">
                        No clients registered yet.
                    </div>
                @endif
            </div>

            {{-- Right column: Appointments + Recent Revenue --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- Upcoming Appointments --}}
                <div class="dash-card">
                    <div class="section-title-row">
                        <h3 class="section-title">
                            <span class="section-title-dot" style="background:#dc2626;"></span>
                            Client Appointments
                        </h3>
                        <a href="{{ route('admin.appointments') }}" class="section-link">
                            View all
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>

                    @if ($upcomingAppointments->isNotEmpty())
                        <div class="appt-list">
                            @foreach ($upcomingAppointments as $appt)
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
                        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.85rem;font-weight:600;">
                            No upcoming appointments.
                        </div>
                    @endif
                </div>

                {{-- Recent Revenue Payments --}}
                <div class="dash-card">
                    <div class="section-title-row">
                        <h3 class="section-title">
                            <span class="section-title-dot" style="background:#16a34a;"></span>
                            Recent Revenue
                        </h3>
                        <a href="{{ route('admin.sales') }}" class="section-link">
                            View all
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>

                    @if ($recentPayments->isNotEmpty())
                        <div class="payment-list">
                            @foreach ($recentPayments as $payment)
                                <div class="payment-item">
                                    <div class="payment-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
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
                        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.85rem;font-weight:600;">
                            No payments recorded yet.
                        </div>
                    @endif
                </div>

            </div>{{-- /right column --}}
        </div>{{-- /bottom-row --}}

    </div>{{-- /dashboard-container --}}
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            // Table toggles
            document.querySelectorAll('[data-chart-table]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var table = document.getElementById(btn.dataset.chartTable);
                    if (!table) return;
                    var opening = table.hasAttribute('hidden');
                    opening ? table.removeAttribute('hidden') : table.setAttribute('hidden', '');
                    btn.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    btn.textContent = opening ? 'Hide table' : 'Table view';
                });
            });

            if (typeof Chart === 'undefined') return;

            var SURFACE      = '#ffffff';
            var INK          = '#0b0b0b';
            var INK_MUTED    = '#94a3b8';
            var INK_SECONDARY= '#52514e';
            var GRID         = '#f1f5f9';
            var AXIS         = '#e2e8f0';
            var SERIES_1     = '#2563eb';
            var SERIES_2     = '#fb923c';
            var SERIES_REV   = '#16a34a';
            var PLAN_COLORS  = ['#2563eb','#fb923c','#16a34a','#7c3aed','#eab308','#0891b2','#64748b','#ec4899'];
            var FONT         = '"Inter","Plus Jakarta Sans",system-ui,sans-serif';

            Chart.defaults.font.family = FONT;
            Chart.defaults.font.size   = 11;
            Chart.defaults.color       = INK_MUTED;

            var tooltip = {
                backgroundColor: '#0f172a',
                titleColor: '#f8fafc',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 10,
                boxWidth: 8,
                boxHeight: 8,
                boxPadding: 4,
                usePointStyle: true,
                titleFont: { family: FONT, size: 12, weight: '700' },
                bodyFont: { family: FONT, size: 12, weight: '500' }
            };

            // ── Line chart: sales revenue ───────────────────────────────────
            var revenueCanvas = document.getElementById('salesRevenueChart');
            if (revenueCanvas) {
                var revenue = @json($salesRevenueTrend);
                new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: revenue.labels,
                        datasets: [{
                            label: 'Collected revenue',
                            data: revenue.values,
                            borderColor: SERIES_REV,
                            backgroundColor: function(ctx) {
                                var g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                                g.addColorStop(0, 'rgba(22,163,74,0.22)');
                                g.addColorStop(1, 'rgba(22,163,74,0.02)');
                                return g;
                            },
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.38,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: SERIES_REV,
                            pointBorderColor: SURFACE,
                            pointBorderWidth: 2,
                            pointHitRadius: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 8, right: 10 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: Object.assign({}, tooltip, {
                                callbacks: {
                                    label: function (ctx) {
                                        return ' Revenue: \u20b1' + Number(ctx.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            })
                        },
                        elements: { line: { borderCapStyle: 'round', borderJoinStyle: 'round' } },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: AXIS },
                                ticks: { color: INK_MUTED, padding: 6, font: { size: 11, weight: '600' } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: GRID, drawTicks: false },
                                border: { display: false },
                                ticks: {
                                    color: INK_MUTED, padding: 8, maxTicksLimit: 6,
                                    callback: function (v) { return '\u20b1' + Number(v).toLocaleString(); },
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            // ── Bar chart: booking trend ───────────────────────────────────
            var trendCanvas = document.getElementById('bookingTrendChart');
            if (trendCanvas) {
                var trend = @json($bookingTrend);
                new Chart(trendCanvas, {
                    type: 'bar',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            { label: 'Total bookings', data: trend.total,    backgroundColor: SERIES_1, borderRadius: 6, borderSkipped: false, maxBarThickness: 16 },
                            { label: 'Approved',       data: trend.approved, backgroundColor: SERIES_2, borderRadius: 6, borderSkipped: false, maxBarThickness: 16 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        layout: { padding: { top: 10 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: tooltip },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: AXIS },
                                ticks: { color: INK_MUTED, padding: 8, maxRotation: 40, minRotation: 0, autoSkip: false, font: { size: 10, weight: '600' } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: GRID, drawTicks: false },
                                border: { display: false },
                                ticks: { color: INK_MUTED, precision: 0, padding: 8, maxTicksLimit: 6, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            // ── Doughnut chart: bookings per plan ──────────────────────────
            var planCanvas = document.getElementById('bookingsByPlanChart');
            if (planCanvas) {
                var plans = @json($bookingsByPlan);
                var pColors = plans.labels.map(function (_, i) { return PLAN_COLORS[i % PLAN_COLORS.length]; });
                new Chart(planCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: plans.labels,
                        datasets: [{
                            label: 'Bookings',
                            data: plans.values,
                            backgroundColor: pColors,
                            hoverBackgroundColor: pColors,
                            borderColor: SURFACE,
                            borderWidth: 3,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '64%',
                        layout: { padding: 4 },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: { color: INK_SECONDARY, usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8, padding: 14, font: { size: 11, weight: '600' } }
                            },
                            tooltip: Object.assign({}, tooltip, {
                                callbacks: {
                                    label: function (ctx) {
                                        var n = ctx.parsed;
                                        var total = ctx.dataset.data.reduce(function (s, v) { return s + Number(v); }, 0);
                                        var pct = total ? Math.round((n / total) * 100) : 0;
                                        return ' ' + ctx.label + ': ' + n + (n === 1 ? ' booking' : ' bookings') + ' (' + pct + '%)';
                                    }
                                }
                            })
                        }
                    }
                });
            }
        })();
    </script>
@endpush
