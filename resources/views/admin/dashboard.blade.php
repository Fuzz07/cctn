@extends('layouts.admin')

@section('title', 'Admin Dashboard - BCTVI Bantayan')

@push('styles')
<style>
    /* Admin Dashboard Dashboard-specific Styles */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        height: 100%;
        min-height: 0;
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
        border: 1px solid #e2e8f0;
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
        color: #64748b;
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
    .dashboard-primary-action:hover { background: #b91c1c; color: #fff; transform: translateY(-1px); }
    .welcome-header h2 {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
    }
    .welcome-header p {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }

    /* 4 Stat Cards Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07); }
    a.stat-card { text-decoration: none; color: inherit; cursor: pointer; }
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
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .stat-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    /* Layout Split Rows */
    .dash-split-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.25rem;
    }

    .dash-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
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
        color: #0f172a;
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
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .table-modern td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    .badge-pill {
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: capitalize;
    }
    .badge-pill-yellow { background: #fef3c7; color: #d97706; }
    .badge-pill-green { background: #dcfce7; color: #15803d; }
    .badge-pill-red { background: #fee2e2; color: #dc2626; }
    .badge-pill-gray { background: #f1f5f9; color: #475569; }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    .action-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
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
        border-color: #cbd5e1;
        box-shadow: 0 6px 15px rgba(0,0,0,0.04);
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
        color: #0f172a;
        margin: 0;
    }

    @media (max-width: 1024px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
        .dash-split-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .stats-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-hero">
        <div class="welcome-header">
            <div class="dashboard-eyebrow"><span class="dashboard-live-dot"></span> Operations overview</div>
            <h2>Welcome back, {{ $admin->fullname ?? 'Admin' }}</h2>
            <p>Monitor bookings, customers, and service activity from one workspace.</p>
            <div class="dashboard-hero-meta">{{ date('l, F j, Y') }} &middot; BCTVI Bantayan</div>
        </div>
        <a class="dashboard-primary-action" href="{{ route('admin.appointments') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Manage bookings
        </a>
    </div>

    @if (session('success_message'))
        <div class="alert alert-success" style="background:#dcfce7; color:#15803d; padding: 1rem; border-radius: 12px; font-size: 0.95rem; font-weight: 500; border: 1px solid #bbf7d0;">
            {{ session('success_message') }}
        </div>
    @endif

    <!-- 4 KPI Stat Cards -->
    <div class="stats-row">
        <!-- Total Bookings -->
        <a href="{{ route('admin.appointments') }}" class="stat-card">
            <div class="stat-icon-wrap" style="background: #fef2f2; color: #dc2626;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div class="stat-meta">
                <span class="stat-title">Total Bookings</span>
                <span class="stat-number">{{ $stats['total'] }}</span>
            </div>
        </a>

        <!-- Pending Approvals -->
        <a href="{{ route('admin.appointments', ['status' => 'pending']) }}" class="stat-card">
            <div class="stat-icon-wrap" style="background: #fff7ed; color: #ea580c;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-meta">
                <span class="stat-title">Pending Approvals</span>
                <span class="stat-number">{{ $stats['pending'] }}</span>
            </div>
        </a>

        <!-- Registered Clients -->
        <a href="{{ route('admin.clients') }}" class="stat-card">
            <div class="stat-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="stat-meta">
                <span class="stat-title">Total Clients</span>
                <span class="stat-number">{{ $stats['clients'] }}</span>
            </div>
        </a>

        <!-- Active Services -->
        <a href="{{ route('admin.services') }}" class="stat-card">
            <div class="stat-icon-wrap" style="background: #eff6ff; color: #2563eb;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            </div>
            <div class="stat-meta">
                <span class="stat-title">Active Services</span>
                <span class="stat-number">{{ $stats['services'] }}</span>
            </div>
        </a>
    </div>

    <div class="dash-split-row">
        <!-- Recent Bookings Table -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3 class="dash-card-title">Recent Booking Requests</h3>
                <a href="{{ route('admin.appointments') }}" style="font-size:0.85rem; font-weight:700; color:#dc2626; text-decoration:none;">View all</a>
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
                                if($appt->status == 'pending') $badgeClass = 'badge-pill-yellow';
                                elseif($appt->status == 'approved') $badgeClass = 'badge-pill-green';
                                elseif($appt->status == 'cancelled') $badgeClass = 'badge-pill-red';
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#0f172a;">{{ $appt->client->firstname }} {{ $appt->client->lastname }}</div>
                                    <div style="font-size:0.75rem; color:#64748b;">{{ $appt->client->contact_no }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $appt->service->service_name }}</div>
                                    <div style="font-size:0.75rem; color:#64748b;">₱{{ number_format($appt->service->price, 2) }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#0f172a;">{{ date('M d, Y', strtotime($appt->preferred_date)) }}</div>
                                    <div style="font-size:0.75rem; color:#dc2626; font-weight:700;">{{ date('g:i A', strtotime($appt->preferred_time)) }}</div>
                                </td>
                                <td>
                                    <span class="badge-pill {{ $badgeClass }}">{{ $appt->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 3rem; color: #94a3b8;">
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="action-title">Manage Appointments</div>
                </a>
                <a href="{{ route('admin.clients') }}" class="action-card">
                    <div class="action-icon" style="background:#eff6ff; color:#2563eb;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="action-title">Client Database</div>
                </a>
                <a href="{{ route('admin.billing') }}" class="action-card">
                    <div class="action-icon" style="background:#f0fdf4; color:#16a34a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div class="action-title">Billing & Payments</div>
                </a>
                <a href="{{ route('admin.services') }}" class="action-card">
                    <div class="action-icon" style="background:#f5f3ff; color:#8b5cf6;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </div>
                    <div class="action-title">Manage Services</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
