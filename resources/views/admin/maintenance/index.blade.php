@extends('layouts.admin')

@section('title', 'Maintenance Requests - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:#0f172a; margin:0; }

    .table-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; text-align:left; font-size:.88rem; }
    .data-table th { padding:.9rem 1rem; background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#64748b; font-weight:700; text-transform:uppercase; font-size:.72rem; letter-spacing:.05em; }
    .data-table td { padding:1rem; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
    .data-table tbody tr:hover { background:#f8fafc; }

    .badge { padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .badge-open { background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; }
    .badge-in-progress { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
    .badge-resolved { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .badge-closed { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; }

    .inline-form { display:flex; gap:.5rem; align-items:center; }
    .select-sm { padding:.4rem .65rem; border:1px solid #e2e8f0; border-radius:6px; font-size:.8rem; font-weight:600; background:#fff; cursor:pointer; }
    .select-sm:focus { outline:none; border-color:#dc2626; }
    .btn-update { padding:.4rem .8rem; border-radius:6px; font-size:.8rem; font-weight:700; background:#0f172a; color:#fff; border:none; cursor:pointer; white-space:nowrap; }
    .btn-update:hover { background:#1e293b; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Maintenance Requests</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Issue Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>Follow-up Note</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                @php
                    $badgeClass = match(strtolower($req->status)) {
                        'open' => 'badge-open',
                        'in progress', 'in-progress' => 'badge-in-progress',
                        'resolved' => 'badge-resolved',
                        default => 'badge-closed'
                    };
                @endphp
                <tr>
                    <td style="font-family:monospace; color:#94a3b8; font-weight:700;">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">{{ $req->client->firstname }} {{ $req->client->lastname }}</div>
                        <div style="font-size:.8rem; color:#64748b;">{{ $req->client->contact_no }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#0f172a;">{{ $req->issue_type ?? 'General' }}</div>
                        <div style="font-size:.75rem; color:#64748b;">{{ $req->created_at->format('M d, Y') }}</div>
                    </td>
                    <td style="max-width:200px;">
                        <div style="font-size:.85rem; color:#475569; white-space:pre-wrap; line-height:1.4;">{{ Str::limit($req->description, 80) }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $req->status }}</span>
                    </td>
                    <td style="max-width:180px;">
                        <div style="font-size:.82rem; color:#64748b; font-style:italic;">{{ $req->follow_up_note ? Str::limit($req->follow_up_note, 60) : '—' }}</div>
                    </td>
                    <td>
                        <form action="{{ route('admin.maintenance.update') }}" method="POST" class="inline-form">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $req->id }}">
                            <select name="status" class="select-sm">
                                <option value="open" {{ $req->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in progress" {{ $req->status == 'in progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $req->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $req->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button type="submit" class="btn-update">Save</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:3rem; color:#94a3b8;">No maintenance requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
