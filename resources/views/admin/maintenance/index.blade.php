@extends('layouts.admin')

@section('title', 'Maintenance Requests - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:var(--text-dark); margin:0; }
    .table-card { background:var(--bg-card); border-radius:8px; border:1px solid var(--border-light); overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; text-align:left; font-size:.88rem; }
    .data-table th { padding:.9rem 1rem; background:var(--bg-page); border-bottom:2px solid var(--border-light); color:var(--text-muted); font-weight:700; text-transform:uppercase; font-size:.72rem; letter-spacing:0; }
    .data-table td { padding:1rem; border-bottom:1px solid var(--bg-subtle); color:var(--text-body); vertical-align:top; }
    .data-table tbody tr:hover { background:var(--bg-page); }
    .badge { display:inline-block; padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .badge-open { background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; }
    .badge-in-progress { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
    .badge-resolved { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .badge-closed { background:var(--bg-subtle); color:var(--text-muted); border:1px solid var(--border-light); }
    .inline-form { display:grid; gap:.5rem; min-width:240px; }
    .select-sm { padding:.5rem .65rem; border:1px solid var(--border-light); border-radius:6px; font-size:.8rem; font-weight:600; background:var(--bg-card); color:var(--text-body); cursor:pointer; }
    .select-sm:focus { outline:none; border-color:#dc2626; }
    .reply-input { width:100%; min-height:72px; resize:vertical; padding:.55rem .65rem; border:1px solid var(--border-light); border-radius:6px; background:var(--bg-card); color:var(--text-body); font:inherit; font-size:.8rem; line-height:1.4; }
    .reply-input:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 2px rgba(220,38,38,.08); }
    .btn-update { min-height:36px; padding:.45rem .8rem; border-radius:6px; font-size:.8rem; font-weight:700; background:#0f172a; color:#fff; border:none; cursor:pointer; }
    .btn-update:hover { background:#1e293b; }
    .message-thread { min-width:250px; max-width:330px; }
    .message-thread summary { cursor:pointer; color:#1d4ed8; font-size:.8rem; font-weight:700; }
    .message-list { display:grid; gap:.5rem; margin-top:.65rem; max-height:220px; overflow-y:auto; }
    .message-item { padding:.55rem .65rem; border-radius:6px; font-size:.78rem; line-height:1.4; white-space:pre-wrap; }
    .message-item--client { background:#f8fafc; border:1px solid #e2e8f0; color:#334155; }
    .message-item--admin { background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; }
    .message-meta { display:block; margin-bottom:.2rem; font-size:.68rem; font-weight:800; text-transform:uppercase; color:inherit; opacity:.75; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Maintenance Requests</h1>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Issue</th>
                <th>Description</th>
                <th>Status</th>
                <th>Messages</th>
                <th>Manage Request</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                @php
                    $normalizedStatus = strtolower($req->status);
                    $badgeClass = match($normalizedStatus) {
                        'open', 'pending' => 'badge-open',
                        'in progress', 'in-progress' => 'badge-in-progress',
                        'resolved' => 'badge-resolved',
                        default => 'badge-closed'
                    };
                @endphp
                <tr>
                    <td style="font-family:monospace; color:var(--text-faint); font-weight:700;">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight:700; color:var(--text-dark);">{{ $req->client->firstname }} {{ $req->client->lastname }}</div>
                        <div style="font-size:.8rem; color:var(--text-muted);">{{ $req->client->contact_no }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:var(--text-dark);">{{ $req->subject }}</div>
                        <div style="font-size:.75rem; color:var(--text-muted);">{{ $req->priority }} priority &middot; {{ $req->created_at->format('M d, Y') }}</div>
                    </td>
                    <td style="min-width:180px; max-width:260px;">
                        <div style="font-size:.85rem; color:var(--text-body); white-space:pre-wrap; line-height:1.4;">{{ $req->description }}</div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $req->status }}</span></td>
                    <td>
                        <details class="message-thread" {{ $req->messages->isNotEmpty() ? 'open' : '' }}>
                            <summary>{{ $req->messages->count() }} {{ Str::plural('message', $req->messages->count()) }}</summary>
                            <div class="message-list">
                                @forelse($req->messages as $message)
                                    <div class="message-item message-item--{{ $message->sender_type }}">
                                        <span class="message-meta">
                                            {{ $message->sender_type === 'admin' ? 'Administrator' : 'Client' }} &middot; {{ $message->created_at->format('M d, g:i A') }}
                                        </span>
                                        {{ $message->message }}
                                    </div>
                                @empty
                                    <span style="font-size:.78rem; color:var(--text-muted);">No follow-up messages yet.</span>
                                @endforelse
                            </div>
                        </details>
                    </td>
                    <td>
                        <form action="{{ route('admin.maintenance.update') }}" method="POST" class="inline-form">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $req->id }}">
                            <select name="status" class="select-sm" aria-label="Maintenance status">
                                <option value="Open" {{ in_array($normalizedStatus, ['open', 'pending']) ? 'selected' : '' }}>Open</option>
                                <option value="In Progress" {{ $normalizedStatus === 'in progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ $normalizedStatus === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="Closed" {{ $normalizedStatus === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <textarea name="reply_message" class="reply-input" maxlength="1000" placeholder="Reply to the client (optional)" aria-label="Reply to client"></textarea>
                            <button type="submit" class="btn-update">Save &amp; Send Reply</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:3rem; color:var(--text-faint);">No maintenance requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
