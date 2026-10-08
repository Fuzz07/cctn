@extends('layouts.admin')

@section('title', 'Admin Dashboard - BCTVI Bantayan')

@push('styles')
    <style>
        /* Admin Dashboard Dashboard-specific Styles */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            height: auto;
            min-height: 100%;
            font-family: system-ui, sans-serif;
            max-width: 1600px;
            margin: 0 auto;
        }

        .dashboard-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.5rem 1.75rem;
            border: 1px solid var(--border-light);
            border-radius: 18px;
            background: linear-gradient(115deg, #ffffff 0%, #fff7f7 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .dashboard-eyebrow {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: #dc2626;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
        }

        .dashboard-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 4px #dcfce7;
        }

        .dashboard-hero-meta {
            margin-top: 0.6rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dashboard-primary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            background: #dc2626;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(220, 38, 38, 0.2);
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .dashboard-primary-action:hover {
            background: #b91c1c;
            color: #fff;
            transform: translateY(-1px);
        }

        .welcome-header h2 {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
        }

        .welcome-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
        }

        /* KPI Stat Cards Grid */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.25rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
        }

        a.stat-card {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .stat-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-meta {
            display: flex;
            flex-direction: column;
        }

        .stat-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.1;
        }

        /* Layout Split Rows */
        .dash-split-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem;
        }

        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
        }

        .dash-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .dash-card-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
        }

        /* Table Custom Badges */
        .table-modern {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .table-modern th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-modern td {
            padding: 1rem;
            border-bottom: 1px solid var(--bg-subtle);
            color: var(--text-body);
            vertical-align: middle;
        }

        .badge-pill {
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-pill-yellow {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-pill-green {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-pill-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-pill-gray {
            background: var(--bg-subtle);
            color: #475569;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .action-card {
            background: var(--bg-card);
            border: 1px solid var(--bg-subtle);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
        }

        .action-card:hover {
            transform: translateY(-2px);
            border-color: var(--border);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.04);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .action-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        /* Analytics Charts */
        .analytics-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .chart-card {
            min-width: 0;
        }

        .chart-subtitle {
            margin: 0.2rem 0 0 0;
            font-size: 0.8rem;
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
            font-size: 0.72rem;
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
            margin-bottom: 0.9rem;
        }

        .chart-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .chart-key {
            width: 16px;
            height: 3px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .chart-canvas-wrap {
            position: relative;
            height: 270px;
            min-width: 0;
        }

        .chart-canvas-wrap--revenue {
            height: 300px;
        }

        .chart-canvas-wrap--doughnut {
            height: 300px;
        }

        .chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 270px;
            color: var(--text-faint, #94a3b8);
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
            padding: 0.5rem 0.65rem;
            text-align: right;
            border-bottom: 1px solid var(--bg-subtle);
            color: var(--text-body);
        }

        .chart-table th:first-child,
        .chart-table td:first-child {
            text-align: left;
        }

        .chart-table thead th {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-light);
        }

        .chart-table[hidden] {
            display: none;
        }

        .chart-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 1024px) {
            .analytics-row {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .dash-split-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-hero">
            <div class="welcome-header">
                <div class="dashboard-eyebrow"><span class="dashboard-live-dot"></span> Operations overview</div>
                <h2>Welcome back, {{ str_ireplace('CCTN', 'BCTVI', $admin->fullname ?? 'BCTVI Admin') }}</h2>
                <p>Monitor bookings, customers, and service activity from one workspace.</p>
                <div class="dashboard-hero-meta">{{ date('l, F j, Y') }} &middot; BCTVI Bantayan</div>
            </div>
            <a class="dashboard-primary-action" href="{{ route('admin.appointments') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Manage bookings
            </a>

        </div>

        @if (session('success_message'))
            <div class="alert alert-success"
                style="background:#dcfce7; color:#15803d; padding: 1rem; border-radius: 12px; font-size: 0.95rem; font-weight: 500; border: 1px solid #bbf7d0;">
                {{ session('success_message') }}
            </div>
        @endif

        <!-- KPI Stat Cards -->
        <div class="stats-row">
            <!-- Total Bookings -->
            <a href="{{ route('admin.appointments') }}" class="stat-card">
                <div class="stat-icon-wrap" style="background: #fef2f2; color: #dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Total Bookings</span>
                    <span class="stat-number">{{ $stats['total'] }}</span>
                </div>
            </a>

            <!-- Pending Approvals -->
            <a href="{{ route('admin.appointments', ['status' => 'pending']) }}" class="stat-card">
                <div class="stat-icon-wrap" style="background: #fff7ed; color: #ea580c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Pending Approvals</span>
                    <span class="stat-number">{{ $stats['pending'] }}</span>
                </div>
            </a>

            <!-- Active Subscribers -->
            <a href="{{ route('admin.clients', ['filter' => 'active']) }}" class="stat-card">
                <div class="stat-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Active Subscribers</span>
                    <span class="stat-number">{{ $stats['active_clients'] }}</span>
                </div>
            </a>

            <!-- Inactive Subscribers -->
            <a href="{{ route('admin.clients', ['filter' => 'inactive']) }}" class="stat-card">
                <div class="stat-icon-wrap" style="background: #fef2f2; color: #b91c1c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M9 9l6 6M15 9l-6 6"></path>
                    </svg>
                </div>
                <div class="stat-meta">
                    <span class="stat-title">Inactive Subscribers</span>
                    <span class="stat-number">{{ $stats['inactive_clients'] }}</span>
                </div>
            </a>

        </div>

        <!-- Analytics -->
        @php
            $revenueHasData = array_sum($salesRevenueTrend['values']) > 0;
            $trendHasData = array_sum($bookingTrend['total']) > 0;
            $planHasData = array_sum($bookingsByPlan['values']) > 0;
        @endphp

        <!-- Line chart: collected sales revenue only -->
        <div class="dash-card chart-card">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">Sales Revenue</h3>
                    <p class="chart-subtitle">Income collected from recorded payments &middot; last 6 months</p>
                </div>
                <button type="button" class="chart-table-toggle" data-chart-table="sales-revenue-table"
                    aria-expanded="false" aria-controls="sales-revenue-table">Table view</button>
            </div>

            @if ($revenueHasData)
                <div class="chart-legend">
                    <span class="chart-legend-item">
                        <span class="chart-key" style="background:#16a34a;"></span> Collected revenue
                    </span>
                </div>
                <div class="chart-canvas-wrap chart-canvas-wrap--revenue">
                    <canvas id="salesRevenueChart" role="img"
                        aria-label="Line chart of collected sales revenue per month for the last six months. The same figures are listed in the table view."></canvas>
                </div>
            @else
                <div class="chart-empty">No sales revenue recorded in the last 6 months yet.</div>
            @endif

            <table class="chart-table" id="sales-revenue-table" hidden>
                <caption class="chart-sr-only">Collected sales revenue per month, last 6 months</caption>
                <thead>
                    <tr>
                        <th scope="col">Month</th>
                        <th scope="col">Revenue</th>
                    </tr>
                </thead>
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

        <div class="analytics-row">
            <!-- Horizontal bar chart: booking volume over time -->
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Booking Trend</h3>
                        <p class="chart-subtitle">Requests received per month &middot; last 6 months</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="booking-trend-table"
                        aria-expanded="false" aria-controls="booking-trend-table">Table view</button>
                </div>

                @if ($trendHasData)
                    <div class="chart-legend">
                        <span class="chart-legend-item">
                            <span class="chart-key" style="background:#2a78d6;"></span> Total bookings
                        </span>
                        <span class="chart-legend-item">
                            <span class="chart-key" style="background:#eb6834;"></span> Approved
                        </span>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="bookingTrendChart" role="img"
                            aria-label="Horizontal bar chart of total and approved bookings per month for the last six months. The same figures are listed in the table view."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No bookings recorded in the last 6 months yet.</div>
                @endif

                <table class="chart-table" id="booking-trend-table" hidden>
                    <caption class="chart-sr-only">Bookings per month, last 6 months</caption>
                    <thead>
                        <tr>
                            <th scope="col">Month</th>
                            <th scope="col">Total</th>
                            <th scope="col">Approved</th>
                        </tr>
                    </thead>
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

            <!-- Doughnut chart: booking volume per plan -->
            <div class="dash-card chart-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Bookings by Plan</h3>
                        <p class="chart-subtitle">Total requests per internet plan</p>
                    </div>
                    <button type="button" class="chart-table-toggle" data-chart-table="bookings-plan-table"
                        aria-expanded="false" aria-controls="bookings-plan-table">Table view</button>
                </div>

                @if ($planHasData)
                    <div class="chart-canvas-wrap chart-canvas-wrap--doughnut">
                        <canvas id="bookingsByPlanChart" role="img"
                            aria-label="Doughnut chart of total bookings per internet plan. The same figures are listed in the table view."></canvas>
                    </div>
                @else
                    <div class="chart-empty">No plan bookings to chart yet.</div>
                @endif

                <table class="chart-table" id="bookings-plan-table" hidden>
                    <caption class="chart-sr-only">Bookings per plan</caption>
                    <thead>
                        <tr>
                            <th scope="col">Plan</th>
                            <th scope="col">Bookings</th>
                        </tr>
                    </thead>
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

        <div class="dash-split-row">
            <!-- Recent Bookings Table -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">Recent Booking Requests</h3>
                    <a href="{{ route('admin.appointments') }}"
                        style="font-size:0.85rem; font-weight:700; color:#dc2626; text-decoration:none;">View all</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Pref. Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentBookings as $appt)
                                @php
                                    $badgeClass = 'badge-pill-gray';
                                    if ($appt->status == 'pending')
                                        $badgeClass = 'badge-pill-yellow';
                                    elseif ($appt->status == 'approved')
                                        $badgeClass = 'badge-pill-green';
                                    elseif ($appt->status == 'cancelled')
                                        $badgeClass = 'badge-pill-red';
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:var(--text-dark);">{{ $appt->client->firstname }}
                                            {{ $appt->client->lastname }}</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $appt->client->contact_no }}
                                        </div>
                                        <div
                                            style="font-size:0.72rem; font-weight:700; margin-top:0.25rem; color:{{ $appt->client->isAccountActive() ? '#15803d' : '#b91c1c' }};">
                                            {{ $appt->client->isAccountActive() ? 'Active' : 'Inactive' }} ·
                                            {{ $appt->client->subscription_status_label }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;">{{ $appt->service->service_name }}</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">
                                            ₱{{ number_format($appt->service->price, 2) }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:var(--text-dark);">
                                            {{ date('M d, Y', strtotime($appt->preferred_date)) }}</div>
                                        <div style="font-size:0.75rem; color:#dc2626; font-weight:700;">
                                            {{ date('g:i A', strtotime($appt->preferred_time)) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-pill {{ $badgeClass }}">{{ $appt->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; padding: 3rem; color: var(--text-faint);">
                                        No recent appointments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">Quick Links</h3>
                </div>

                <div class="quick-actions-grid">
                    <a href="{{ route('admin.appointments') }}" class="action-card">
                        <div class="action-icon" style="background:#fef2f2; color:#dc2626;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </div>
                        <div class="action-title">Manage Appointments</div>
                    </a>
                    <a href="{{ route('admin.clients') }}" class="action-card">
                        <div class="action-icon" style="background:#eff6ff; color:#2563eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="action-title">Client Database</div>
                    </a>
                    <a href="{{ route('admin.billing') }}" class="action-card">
                        <div class="action-icon" style="background:#f0fdf4; color:#16a34a;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="action-title">Billing & Payments</div>
                    </a>
                    <a href="{{ route('admin.services') }}" class="action-card">
                        <div class="action-icon" style="background:#f5f3ff; color:#8b5cf6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="action-title">Manage Services</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Chart.js 4.4.1, vendored so the dashboard charts also work offline --}}
    <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            // Every chart ships a table twin, so no value is reachable by hover alone.
            document.querySelectorAll('[data-chart-table]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var table = document.getElementById(btn.dataset.chartTable);
                    if (!table) return;

                    var opening = table.hasAttribute('hidden');
                    if (opening) {
                        table.removeAttribute('hidden');
                    } else {
                        table.setAttribute('hidden', '');
                    }
                    btn.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    btn.textContent = opening ? 'Hide table' : 'Table view';
                });
            });

            if (typeof Chart === 'undefined') return;

            var SURFACE = '#ffffff';
            var INK = '#0b0b0b';
            var INK_SECONDARY = '#52514e';
            var INK_MUTED = '#898781';
            var GRID = '#e1e0d9';
            var AXIS = '#c3c2b7';
            var SERIES_1 = '#2a78d6';
            var SERIES_2 = '#eb6834';
            var SERIES_REVENUE = '#16a34a';
            var PLAN_COLORS = ['#2a78d6', '#eb6834', '#16a34a', '#8b5cf6', '#eab308', '#0891b2', '#64748b'];
            var FONT = 'Inter, system-ui, -apple-system, "Segoe UI", sans-serif';

            Chart.defaults.font.family = FONT;
            Chart.defaults.font.size = 11;
            Chart.defaults.color = INK_MUTED;

            var tooltip = {
                backgroundColor: INK,
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                padding: 10,
                cornerRadius: 8,
                boxWidth: 8,
                boxHeight: 8,
                boxPadding: 4,
                usePointStyle: true,
                titleFont: { family: FONT, size: 12, weight: '700' },
                bodyFont: { family: FONT, size: 12, weight: '500' }
            };

            // ---- Line chart: collected sales revenue ---------------------
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
                            borderColor: SERIES_REVENUE,
                            backgroundColor: 'rgba(22, 163, 74, 0.10)',
                            fill: true,
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: SERIES_REVENUE,
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
                                        return ' Revenue: \u20b1' + Number(ctx.parsed.y).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
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
                                    color: INK_MUTED,
                                    padding: 8,
                                    maxTicksLimit: 6,
                                    callback: function (value) {
                                        return '\u20b1' + Number(value).toLocaleString();
                                    },
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            // ---- Horizontal bar chart: booking trend ---------------------
            var trendCanvas = document.getElementById('bookingTrendChart');

            if (trendCanvas) {
                var trend = @json($bookingTrend);

                new Chart(trendCanvas, {
                    type: 'bar',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            {
                                label: 'Total bookings',
                                data: trend.total,
                                backgroundColor: SERIES_1,
                                borderRadius: 4,
                                borderSkipped: false,
                                maxBarThickness: 18
                            },
                            {
                                label: 'Approved',
                                data: trend.approved,
                                backgroundColor: SERIES_2,
                                borderRadius: 4,
                                borderSkipped: false,
                                maxBarThickness: 18
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { right: 10 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: tooltip
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: GRID, drawTicks: false },
                                border: { display: false },
                                ticks: {
                                    color: INK_MUTED,
                                    precision: 0,
                                    padding: 8,
                                    maxTicksLimit: 6,
                                    font: { size: 11 }
                                }
                            },
                            y: {
                                grid: { display: false },
                                border: { color: AXIS },
                                ticks: {
                                    color: INK_MUTED,
                                    padding: 8,
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            }

            // ---- Doughnut chart: bookings per plan -----------------------
            var planCanvas = document.getElementById('bookingsByPlanChart');

            if (planCanvas) {
                var plans = @json($bookingsByPlan);
                var planColors = plans.labels.map(function (_, index) {
                    return PLAN_COLORS[index % PLAN_COLORS.length];
                });

                new Chart(planCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: plans.labels,
                        datasets: [{
                            label: 'Bookings',
                            data: plans.values,
                            backgroundColor: planColors,
                            hoverBackgroundColor: planColors,
                            borderColor: SURFACE,
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        layout: { padding: 4 },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: {
                                    color: INK_SECONDARY,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    boxWidth: 8,
                                    boxHeight: 8,
                                    padding: 14,
                                    font: { size: 11, weight: '600' }
                                }
                            },
                            tooltip: Object.assign({}, tooltip, {
                                callbacks: {
                                    label: function (ctx) {
                                        var n = ctx.parsed;
                                        var total = ctx.dataset.data.reduce(function (sum, value) {
                                            return sum + Number(value);
                                        }, 0);
                                        var percentage = total ? Math.round((n / total) * 100) : 0;
                                        return ' ' + ctx.label + ': ' + n +
                                            (n === 1 ? ' booking' : ' bookings') + ' (' + percentage + '%)';
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
