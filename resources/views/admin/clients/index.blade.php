@extends('layouts.admin')

@section('title', 'Client Database - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stat-meta { display: flex; flex-direction: column; }
    .stat-title { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem; }
    .stat-val { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); line-height: 1; }

    .filter-card { background: var(--bg-card); border-radius: 12px; padding: 1.25rem; border: 1px solid var(--border-light); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .filter-pills { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.25rem; }
    .filter-pill { padding: 0.5rem 1rem; border-radius: 99px; background: var(--bg-page); border: 1px solid var(--border-light); color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .filter-pill:hover { background: var(--border-light); color: var(--text-dark); }
    .filter-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    .search-form { display: flex; gap: 0.5rem; min-width: 300px; }
    .search-input { flex: 1; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; }
    .search-input:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    .btn-search { background: #dc2626; color: #fff; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; }

    .table-card { background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-light); overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
    .data-table th { padding: 1rem; background: var(--bg-page); border-bottom: 2px solid var(--border-light); color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .data-table td { padding: 1rem; border-bottom: 1px solid var(--bg-subtle); color: var(--text-body); vertical-align: middle; }
    .data-table tbody tr:hover { background: var(--bg-page); }

    .btn-row { padding: 0.4rem 0.9rem; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; white-space: nowrap; background: var(--bg-card); }
    .btn-archive { color: #b45309; border: 1px solid #fde68a; }
    .btn-archive:hover { background: #fef9c3; }
    .btn-restore { color: #15803d; border: 1px solid #bbf7d0; }
    .btn-restore:hover { background: #f0fdf4; }
    .archived-tag { display: inline-block; margin-top: 0.25rem; background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-light); padding: 0.1rem 0.55rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; }

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
            <span class="stat-val" id="stat-total-clients">{{ $totalClients }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-title">New This Month</span>
            <span class="stat-val" id="stat-new-this-month">{{ $newThisMonth }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-title">Active Bookings</span>
            <span class="stat-val" id="stat-active-bookings">{{ $activeBookings }}</span>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-pills">
        <a href="{{ route('admin.clients', ['filter' => 'all', 'search' => $search]) }}" class="filter-pill {{ $filter == 'all' ? 'active' : '' }}">All Clients</a>
        <a href="{{ route('admin.clients', ['filter' => 'active_bookings', 'search' => $search]) }}" class="filter-pill {{ $filter == 'active_bookings' ? 'active' : '' }}">With Active Bookings</a>
        <a href="{{ route('admin.clients', ['filter' => 'new_this_month', 'search' => $search]) }}" class="filter-pill {{ $filter == 'new_this_month' ? 'active' : '' }}">Joined This Month</a>
        @if ($archivingSupported)
            <a href="{{ route('admin.clients', ['filter' => 'archived', 'search' => $search]) }}" class="filter-pill {{ $filter == 'archived' ? 'active' : '' }}">Archived ({{ $archivedCount }})</a>
        @else
            <span class="filter-pill" title="Run the archive migration to enable this feature" style="opacity:0.65; cursor:not-allowed;">Archived unavailable</span>
        @endif
    </div>
    <form action="{{ route('admin.clients') }}" method="GET" class="search-form">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="text" name="search" class="search-input" placeholder="Search name, username, email..." value="{{ $search }}">
        <button type="submit" class="btn-search">Search</button>
        @if($search)
            <a href="{{ route('admin.clients', ['filter' => $filter]) }}" class="btn-search" style="background:var(--bg-subtle); color:var(--text-muted); text-decoration:none;">Clear</a>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td>
                        <div style="font-weight:700; color:var(--text-dark);">{{ $client->firstname }} {{ $client->lastname }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ '@' . $client->username }} &middot; Acc: {{ $client->account_number ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:var(--text-body);">{{ $client->email }}</div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">{{ $client->contact_no }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:var(--text-body);">{{ $client->address_barangay }}</div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">{{ $client->address_municipality }}, {{ $client->address_province }}</div>
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
                        <div style="color:var(--text-muted);">{{ $client->created_at->format('M d, Y') }}</div>
                        @if (! $archivingSupported)
                            <span style="font-size:0.75rem; color:var(--text-muted);">Archive migration required</span>
                        @elseif ($client->isArchived())
                            <span class="archived-tag">Archived {{ $client->archived_at->format('M d, Y') }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($client->isArchived())
                            <form action="{{ route('admin.clients.restore', $client->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-row btn-restore">Restore</button>
                            </form>
                        @else
                            <form action="{{ route('admin.clients.archive', $client->id) }}" method="POST"
                                  onsubmit="return confirm({{ json_encode('Archive ' . $client->firstname . ' ' . $client->lastname . '? They will no longer be able to sign in. Their booking and payment history is kept, and you can restore them anytime.') }});">
                                @csrf
                                <button type="submit" class="btn-row btn-archive">Archive</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-faint);">
                        {{ $filter === 'archived' && !$search ? 'No archived clients.' : 'No clients found matching your search criteria.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const POLL_INTERVAL = 30000; // 30 seconds
    const STATS_URL     = '{{ route("admin.clients.stats") }}';

    const elTotal   = document.getElementById('stat-total-clients');
    const elNew     = document.getElementById('stat-new-this-month');
    const elActive  = document.getElementById('stat-active-bookings');

    function animateUpdate(el, newVal) {
        const current = parseInt(el.textContent, 10);
        if (current === newVal) return;

        el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-6px)';

        setTimeout(function () {
            el.textContent  = newVal;
            el.style.opacity   = '1';
            el.style.transform = 'translateY(0)';
        }, 260);
    }

    function fetchStats() {
        fetch(STATS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.ok ? res.json() : Promise.reject(res.status); })
        .then(function (data) {
            animateUpdate(elTotal,  data.total_clients);
            animateUpdate(elNew,    data.new_this_month);
            animateUpdate(elActive, data.active_bookings);
        })
        .catch(function (err) {
            console.warn('[Client Stats] Poll failed:', err);
        });
    }

    // Start polling after first interval
    setInterval(fetchStats, POLL_INTERVAL);
})();
</script>
@endpush
