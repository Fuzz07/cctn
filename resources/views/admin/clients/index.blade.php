@extends('layouts.admin')

@section('title', 'Client Database - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    
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
    .status-tag { display: inline-block; padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .status-tag--active { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .status-tag--inactive { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .status-tag--pending { background: #fffbeb; color: #a16207; border: 1px solid #fde68a; margin-top: 0.35rem; }
    .request-actions { display: flex; flex-direction: column; gap: 0.45rem; min-width: 155px; }
    .btn-approve-request { color: #fff; background: #dc2626; border: 1px solid #dc2626; }
    .btn-reject-request { color: #475569; background: var(--bg-card); border: 1px solid var(--border-light); }

    .unsubscribe-modal { display: none; position: fixed; inset: 0; z-index: 100000; align-items: center; justify-content: center; padding: 1rem; }
    .unsubscribe-modal.is-open { display: flex; }
    .unsubscribe-modal__backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
    .unsubscribe-modal__dialog { position: relative; width: min(100%, 430px); padding: 2rem; background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.1); text-align: center; animation: unsubscribe-modal-in 0.22s cubic-bezier(.34,1.56,.64,1) both; }
    .unsubscribe-modal__icon { width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.1rem; border-radius: 50%; background: #fef2f2; color: #dc2626; }
    .unsubscribe-modal__title { margin: 0 0 0.4rem; font-size: 1.1rem; font-weight: 700; color: #0f172a; }
    .unsubscribe-modal__message { margin: 0 0 1.6rem; color: #64748b; font-size: 0.9rem; line-height: 1.5; }
    .unsubscribe-modal__actions { display: flex; gap: 0.75rem; }
    .unsubscribe-modal__button { flex: 1; padding: 0.65rem 1rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
    .unsubscribe-modal__button--cancel { border: 1.5px solid #e2e8f0; background: #f8fafc; color: #334155; }
    .unsubscribe-modal__button--confirm { border: none; background: #dc2626; color: #fff; }
    @keyframes unsubscribe-modal-in { from { opacity: 0; transform: scale(0.88) translateY(12px); } to { opacity: 1; transform: scale(1) translateY(0); } }

</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Client Database</h1>
</div>

<div class="filter-card">
    <div class="filter-pills">
        <a href="{{ route('admin.clients', ['filter' => 'all', 'search' => $search]) }}" class="filter-pill {{ in_array($filter, ['all', 'active']) ? 'active' : '' }}">Active Clients</a>
        <a href="{{ route('admin.clients', ['filter' => 'active_bookings', 'search' => $search]) }}" class="filter-pill {{ $filter == 'active_bookings' ? 'active' : '' }}">With Active Bookings</a>
        <a href="{{ route('admin.clients', ['filter' => 'new_this_month', 'search' => $search]) }}" class="filter-pill {{ $filter == 'new_this_month' ? 'active' : '' }}">Joined This Month</a>
        @if ($archivingSupported)
            <a href="{{ route('admin.clients', ['filter' => 'inactive', 'search' => $search]) }}" class="filter-pill {{ in_array($filter, ['inactive', 'archived']) ? 'active' : '' }}">Inactive ({{ $archivedCount }})</a>
        @else
            <span class="filter-pill" title="Run the unsubscribe migration to enable this feature" style="opacity:0.65; cursor:not-allowed;">Inactive unavailable</span>
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
                <th>Current Plan</th>
                <th>Status</th>
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
                            <a href="{{ route('admin.clients.proof-of-billing', $client->id) }}" target="_blank" rel="noopener" style="display:inline-block; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.75rem; font-weight:700; text-decoration:none;">
                                View Proof of Billing
                            </a>
                        @else
                            <span style="display:inline-block; background:#fef9c3; color:#a16207; border:1px solid #fde68a; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.75rem; font-weight:700;">
                                Not Submitted
                            </span>
                        @endif
                        <form action="{{ route('admin.clients.proof-of-billing.update', $client->id) }}" method="POST" enctype="multipart/form-data" style="display:grid; gap:0.35rem; margin-top:0.5rem; min-width:150px;">
                            @csrf
                            <input type="file" name="proof_of_billing" accept="image/jpeg,image/png,image/webp" required style="width:150px; font-size:0.7rem; color:var(--text-muted);">
                            <button type="submit" class="btn-row" style="border:1px solid var(--border-light); background:var(--bg-card); color:var(--text-body);">
                                {{ $client->proof_of_billing ? 'Replace Proof' : 'Upload Proof' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="font-weight:700; color:var(--text-dark);">{{ $client->currentService?->service_name ?? 'No plan assigned' }}</div>
                        @if($client->subscription_ends_at)
                            <div style="font-size:0.75rem; color:var(--text-muted);">Ends {{ $client->subscription_ends_at->format('M d, Y') }}</div>
                        @endif
                    </td>
                    <td>
                        @if (! $client->isAccountActive())
                            <span class="status-tag status-tag--inactive">Inactive</span>
                            <div style="font-size:0.75rem; color:#b91c1c; font-weight:600; margin-top:0.35rem;">{{ $client->subscription_status_label }}</div>
                        @else
                            <span class="status-tag status-tag--active">Active</span>
                            <div style="font-size:0.75rem; color:#15803d; font-weight:600; margin-top:0.35rem;">{{ $client->subscription_status_label }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="color:var(--text-muted);">{{ $client->created_at->format('M d, Y') }}</div>
                        @if (! $archivingSupported)
                            <span style="font-size:0.75rem; color:var(--text-muted);">Archive migration required</span>
                        @endif
                    </td>
                    <td>
                        @if (! $client->isAccountActive() && $client->currentService && $client->currentAppointment)
                            <form action="{{ route('admin.clients.restore', $client->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-row btn-restore">Re-subscribe</button>
                            </form>
                        @elseif (! $client->isAccountActive())
                            <a href="{{ route('admin.appointments') }}" class="btn-row btn-restore" style="display:inline-block; text-decoration:none;">Approve a plan</a>
                        @else
                            <form action="{{ route('admin.clients.archive', $client->id) }}" method="POST"
                                  data-client-name="{{ $client->firstname }} {{ $client->lastname }}"
                                  onsubmit="return showUnsubscribeModal(this);">
                                @csrf
                                <button type="submit" class="btn-row btn-archive">Deactivate Subscription</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-faint);">
                        {{ in_array($filter, ['inactive', 'archived']) && !$search ? 'No inactive clients.' : 'No clients found matching your search criteria.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.partials.simple-pagination', [
    'paginator' => $clients,
    'label' => 'Clients pagination',
])

<div id="unsubscribe-modal" class="unsubscribe-modal" role="dialog" aria-modal="true" aria-labelledby="unsubscribe-modal-title" aria-describedby="unsubscribe-modal-message" aria-hidden="true">
    <div class="unsubscribe-modal__backdrop" onclick="hideUnsubscribeModal()"></div>
    <div class="unsubscribe-modal__dialog">
        <div class="unsubscribe-modal__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v4M14 11v4"/></svg>
        </div>
        <h2 id="unsubscribe-modal-title" class="unsubscribe-modal__title">Deactivate Subscription?</h2>
        <p id="unsubscribe-modal-message" class="unsubscribe-modal__message"></p>
        <div class="unsubscribe-modal__actions">
            <button type="button" class="unsubscribe-modal__button unsubscribe-modal__button--cancel" onclick="hideUnsubscribeModal()">Cancel</button>
            <button type="button" id="unsubscribe-modal-confirm" class="unsubscribe-modal__button unsubscribe-modal__button--confirm" onclick="confirmUnsubscribe()">Deactivate</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var unsubscribeForm = null;
var unsubscribeTrigger = null;

function showUnsubscribeModal(form) {
    unsubscribeForm = form;
    unsubscribeTrigger = document.activeElement;

    var clientName = form.dataset.clientName;
    document.getElementById('unsubscribe-modal-message').textContent =
        'Deactivate ' + clientName + '\'s subscription? Their account will become Inactive. Booking and payment history will be kept.';

    var modal = document.getElementById('unsubscribe-modal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    document.getElementById('unsubscribe-modal-confirm').focus();
    return false;
}

function hideUnsubscribeModal() {
    var modal = document.getElementById('unsubscribe-modal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (unsubscribeTrigger) unsubscribeTrigger.focus();
    unsubscribeForm = null;
    unsubscribeTrigger = null;
}

function confirmUnsubscribe() {
    if (unsubscribeForm) {
        unsubscribeForm.onsubmit = null;
        unsubscribeForm.submit();
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.getElementById('unsubscribe-modal').classList.contains('is-open')) {
        hideUnsubscribeModal();
    }
});
</script>
@endpush
