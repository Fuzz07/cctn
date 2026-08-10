@extends('layouts.admin')

@section('title', 'Schedule Management - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:#0f172a; margin:0; }
    .content-grid { display:grid; grid-template-columns:340px 1fr; gap:1.5rem; }
    @media(max-width:1024px){ .content-grid { grid-template-columns:1fr; } }

    .form-card { background:#fff; border-radius:16px; padding:1.75rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); }
    .form-card-title { font-size:1.1rem; font-weight:800; color:#0f172a; margin:0 0 1.5rem; display:flex; align-items:center; gap:.5rem; }
    .form-group { margin-bottom:1.25rem; }
    .form-label { display:block; font-size:.85rem; font-weight:700; color:#334155; margin-bottom:.5rem; }
    .form-control { width:100%; padding:.75rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; box-sizing:border-box; font-family:inherit; }
    .form-control:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
    .btn-submit { width:100%; background:#dc2626; color:#fff; padding:.85rem; border-radius:8px; font-weight:700; font-size:1rem; border:none; cursor:pointer; transition:all .2s; }
    .btn-submit:hover { background:#b91c1c; }

    .slots-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem; }
    .slot-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; }
    .slot-time { font-size:1.1rem; font-weight:800; color:#0f172a; }
    .slot-actions { display:flex; gap:.5rem; align-items:center; }
    .btn-toggle { padding:.35rem .8rem; border-radius:6px; font-size:.78rem; font-weight:700; cursor:pointer; border:1px solid transparent; }
    .btn-enable { background:#dcfce7; color:#15803d; border-color:#bbf7d0; }
    .btn-enable:hover { background:#bbf7d0; }
    .btn-disable { background:#fef3c7; color:#d97706; border-color:#fde68a; }
    .btn-disable:hover { background:#fde68a; }
    .btn-del { background:#fef2f2; color:#dc2626; border-color:#fecaca; padding:.35rem .6rem; border-radius:6px; font-size:.78rem; font-weight:700; cursor:pointer; border:1px solid transparent; }
    .btn-del:hover { background:#fecaca; }
    .badge-avail { background:#dcfce7; color:#15803d; padding:.25rem .6rem; border-radius:99px; font-size:.72rem; font-weight:700; }
    .badge-unavail { background:#f1f5f9; color:#94a3b8; padding:.25rem .6rem; border-radius:99px; font-size:.72rem; font-weight:700; text-decoration:line-through; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Time Slot Management</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif
@if($errors->any())
    <div style="background:#fef2f2;color:#b91c1c;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #fecaca;">
        @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
    </div>
@endif

<div class="content-grid">
    <!-- Add Slot Form -->
    <div>
        <div class="form-card">
            <h3 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Add New Time Slot
            </h3>
            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Time Slot *</label>
                    <input type="time" name="slot_time" class="form-control" value="{{ old('slot_time') }}" required>
                    <div style="font-size:.75rem; color:#64748b; margin-top:.4rem;">Select a time to add as a bookable appointment slot.</div>
                </div>
                <button type="submit" class="btn-submit">+ Add Time Slot</button>
            </form>
        </div>
    </div>

    <!-- Slot Cards -->
    <div>
        @if($slots->isEmpty())
            <div style="text-align:center;padding:4rem;background:#fff;border-radius:16px;border:1px dashed #cbd5e1;">
                <p style="color:#94a3b8;font-size:1rem;font-weight:600;">No time slots have been configured yet.</p>
            </div>
        @else
            <div class="slots-list">
                @foreach($slots as $slot)
                    <div class="slot-card">
                        <div>
                            <div class="slot-time">{{ date('g:i A', strtotime($slot->slot_time)) }}</div>
                            <div style="margin-top:.35rem;">
                                @if($slot->is_available)
                                    <span class="badge-avail">Available</span>
                                @else
                                    <span class="badge-unavail">Disabled</span>
                                @endif
                            </div>
                        </div>
                        <div class="slot-actions">
                            <!-- Toggle Status -->
                            <form action="{{ route('admin.schedules.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                                <input type="hidden" name="new_status" value="{{ $slot->is_available ? 0 : 1 }}">
                                <button type="submit" class="btn-toggle {{ $slot->is_available ? 'btn-disable' : 'btn-enable' }}">
                                    {{ $slot->is_available ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <!-- Delete Slot -->
                            <form action="{{ route('admin.schedules.delete') }}" method="POST" onsubmit="return confirm('Delete this time slot?');">
                                @csrf
                                <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                                <button type="submit" class="btn-del">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
