@extends('layouts.app')

@section('title', 'My Appointments - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-subtitle { color: #64748b; font-size: 0.9rem; margin-top: 0.25rem; }
    
    .filter-pills { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem; }
    .filter-pill { padding: 0.5rem 1rem; border-radius: 99px; background: #fff; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .filter-pill:hover { background: #f1f5f9; color: #0f172a; }
    .filter-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    .appt-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; }
    .appt-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    
    .appt-header { padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #fafbfc; }
    .appt-ref { font-family: monospace; font-weight: 700; color: #94a3b8; font-size: 0.9rem; }
    
    .appt-body { padding: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 640px) { .appt-body { grid-template-columns: 1fr; } }
    
    .appt-detail { display: flex; flex-direction: column; gap: 0.25rem; }
    .appt-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em; }
    .appt-value { font-size: 1rem; font-weight: 600; color: #0f172a; }
    
    .status-badge { padding: 0.35rem 0.85rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.35rem; }
    .status-badge.pending { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .status-badge.approved { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .status-badge.cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .status-badge.completed { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

    .appt-footer { padding: 1rem 1.5rem; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem; }
    .btn-action { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; }
    .btn-outline-danger { background: #fff; color: #dc2626; border-color: #fca5a5; }
    .btn-outline-danger:hover { background: #fef2f2; }
</style>
@endpush

@section('content')
<div style="max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem;">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Appointments</h1>
            <p class="page-subtitle">Track your booking requests and history</p>
        </div>
        <a href="{{ route('client.book') }}" class="btn" style="background:#0f172a; color:#fff; padding:0.75rem 1.5rem; border-radius:8px; text-decoration:none; font-weight:700;">+ Book New</a>
    </div>

    @php
        $statusFilter = request('status', 'all');
        $filteredAppts = $appointments->filter(function($a) use ($statusFilter) {
            return $statusFilter === 'all' || $a->status === $statusFilter;
        });
    @endphp

    <div class="filter-pills" style="margin-bottom: 1.5rem;">
        <a href="{{ route('client.appointments', ['status' => 'all']) }}" class="filter-pill {{ $statusFilter == 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('client.appointments', ['status' => 'pending']) }}" class="filter-pill {{ $statusFilter == 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('client.appointments', ['status' => 'approved']) }}" class="filter-pill {{ $statusFilter == 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('client.appointments', ['status' => 'cancelled']) }}" class="filter-pill {{ $statusFilter == 'cancelled' ? 'active' : '' }}">Cancelled</a>
    </div>

    <div>
        @forelse ($filteredAppts as $appt)
            <div class="appt-card">
                <div class="appt-header">
                    <span class="appt-ref">REF #{{ str_pad($appt->id, 6, '0', STR_PAD_LEFT) }}</span>
                    <span class="status-badge {{ $appt->status }}">
                        @if($appt->status == 'pending')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @elseif($appt->status == 'approved')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                        {{ $appt->status }}
                    </span>
                </div>
                <div class="appt-body">
                    <div class="appt-detail">
                        <span class="appt-label">Service Required</span>
                        <span class="appt-value">{{ $appt->service->service_name }}</span>
                    </div>
                    <div class="appt-detail">
                        <span class="appt-label">Schedule Date & Time</span>
                        <span class="appt-value" style="color: #dc2626;">{{ date('l, M d, Y', strtotime($appt->preferred_date)) }} at {{ date('h:i A', strtotime($appt->preferred_time)) }}</span>
                    </div>
                    @if($appt->message)
                        <div class="appt-detail" style="grid-column: 1 / -1;">
                            <span class="appt-label">Your Note</span>
                            <span class="appt-value" style="font-weight: 500; font-size: 0.9rem;">{{ $appt->message }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 4rem 1rem; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 1rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #334155;">No appointments found</h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">You don't have any appointments matching this filter.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
