@extends('layouts.admin')

@section('title', 'Client Database - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stat-meta { display: flex; flex-direction: column; }
    .stat-title { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem; }
    .stat-val { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1; }

    .filter-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .filter-pills { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.25rem; }
    .filter-pill { padding: 0.5rem 1rem; border-radius: 99px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .filter-pill:hover { background: #e2e8f0; color: #0f172a; }
    .filter-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    .search-form { display: flex; gap: 0.5rem; min-width: 300px; }
    .search-input { flex: 1; padding: 0.6rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; }
    .search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    .btn-search { background: #dc2626; color: #fff; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; }

    .table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
    .data-table th { padding: 1rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
    .data-table tbody tr:hover { background: #f8fafc; }
    
    .client-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #e2e8f0; }
    
    @media (max-width: 1024px) { .stats-row { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Client Database</h1>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-title">Total Registered</span>
            <span class="stat-val">{{ $totalClients }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-title">New This Month</span>
            <span class="stat-val">{{ $newThisMonth }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-title">Active Bookings</span>
            <span class="stat-val">{{ $activeBookings }}</span>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-pills">
        <a href="{{ route('admin.clients', ['filter' => 'all', 'search' => $search]) }}" class="filter-pill {{ $filter == 'all' ? 'active' : '' }}">All Clients</a>
        <a href="{{ route('admin.clients', ['filter' => 'active_bookings', 'search' => $search]) }}" class="filter-pill {{ $filter == 'active_bookings' ? 'active' : '' }}">With Active Bookings</a>
        <a href="{{ route('admin.clients', ['filter' => 'new_this_month', 'search' => $search]) }}" class="filter-pill {{ $filter == 'new_this_month' ? 'active' : '' }}">Joined This Month</a>
    </div>
    <form action="{{ route('admin.clients') }}" method="GET" class="search-form">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="text" name="search" class="search-input" placeholder="Search name, username, email..." value="{{ $search }}">
        <button type="submit" class="btn-search">Search</button>
        @if($search)
            <a href="{{ route('admin.clients', ['filter' => $filter]) }}" class="btn-search" style="background:#f1f5f9; color:#64748b; text-decoration:none;">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Contact</th>
                <th>Location</th>
                <th>Verification</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <img src="{{ $client->profile_photo ? asset($client->profile_photo) : asset('assets/img/default-avatar.svg') }}" alt="Avatar" class="client-avatar">
                            <div>
                                <div style="font-weight:700; color:#0f172a;">{{ $client->firstname }} {{ $client->lastname }}</div>
                                <div style="font-size:0.75rem; color:#64748b;">{{ '@' . $client->username }} &middot; Acc: {{ $client->account_number ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#334155;">{{ $client->email }}</div>
                        <div style="font-size:0.8rem; color:#64748b;">{{ $client->contact_no }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#334155;">{{ $client->address_barangay }}</div>
                        <div style="font-size:0.8rem; color:#64748b;">{{ $client->address_municipality }}, {{ $client->address_province }}</div>
                    </td>
                    <td>
                        @if ($client->proof_of_billing)
                            <a href="{{ asset($client->proof_of_billing) }}" target="_blank" style="display:inline-block; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.75rem; font-weight:700; text-decoration:none;">
                                View Proof of Billing
                            </a>
                        @else
                            <span style="display:inline-block; background:#fef9c3; color:#a16207; border:1px solid #fde68a; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.75rem; font-weight:700;">
                                Not Submitted
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="color:#64748b;">{{ $client->created_at->format('M d, Y') }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No clients found matching your search criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
