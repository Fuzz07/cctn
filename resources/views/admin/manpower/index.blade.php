@extends('layouts.admin')

@section('title', 'Technical Manpower - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:#0f172a; margin:0; }
    .content-grid { display:grid; grid-template-columns:360px 1fr; gap:1.5rem; }
    @media(max-width:1024px){ .content-grid { grid-template-columns:1fr; } }

    .form-card { background:#fff; border-radius:16px; padding:1.75rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); }
    .form-card-title { font-size:1.1rem; font-weight:800; color:#0f172a; margin:0 0 1.5rem; display:flex; align-items:center; gap:.5rem; }
    .form-group { margin-bottom:1.25rem; }
    .form-label { display:block; font-size:.85rem; font-weight:700; color:#334155; margin-bottom:.5rem; }
    .form-control { width:100%; padding:.75rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; box-sizing:border-box; font-family:inherit; }
    .form-control:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
    .btn-submit { width:100%; background:#dc2626; color:#fff; padding:.85rem; border-radius:8px; font-weight:700; font-size:1rem; border:none; cursor:pointer; margin-top:.5rem; transition:all .2s; }
    .btn-submit:hover { background:#b91c1c; }

    .staff-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1rem; }
    .staff-card { background:#fff; border-radius:14px; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); transition:all .2s; }
    .staff-card:hover { border-color:#cbd5e1; box-shadow:0 6px 20px rgba(0,0,0,0.06); }
    .staff-avatar { width:52px; height:52px; border-radius:50%; background:#0f172a; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.25rem; font-weight:800; margin-bottom:1rem; }
    .staff-name { font-size:1rem; font-weight:700; color:#0f172a; margin-bottom:.25rem; }
    .staff-role { font-size:.82rem; font-weight:600; color:#64748b; margin-bottom:1rem; }
    .staff-footer { display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:.75rem; }

    .badge { padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; }
    .badge-available { background:#dcfce7; color:#15803d; }
    .badge-busy { background:#fef3c7; color:#d97706; }
    .badge-off { background:#f1f5f9; color:#64748b; }

    .quick-status-form { display:flex; gap:.35rem; align-items:center; }
    .quick-status-select { padding:.3rem .6rem; border:1px solid #e2e8f0; border-radius:6px; font-size:.78rem; font-weight:600; background:#fff; cursor:pointer; }
    .btn-update { padding:.3rem .65rem; border-radius:6px; font-size:.78rem; font-weight:700; background:#0f172a; color:#fff; border:none; cursor:pointer; }
    .btn-update:hover { background:#1e293b; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Technical Manpower</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif

<div class="content-grid">
    <!-- Add Staff Form -->
    <div>
        <div class="form-card">
            <h3 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Add Staff Member
            </h3>
            <form action="{{ route('admin.manpower.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Juan Dela Cruz">
                </div>
                <div class="form-group">
                    <label class="form-label">Role / Designation *</label>
                    <input type="text" name="role" class="form-control" value="{{ old('role') }}" required placeholder="e.g. Fiber Technician, Network Engineer">
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Availability</label>
                    <select name="availability" class="form-control">
                        <option value="Available">Available</option>
                        <option value="Busy">Busy</option>
                        <option value="Off Duty">Off Duty</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional remarks...">
                </div>
                <button type="submit" class="btn-submit">Add Staff Member</button>
            </form>
        </div>
    </div>

    <!-- Staff Cards -->
    <div>
        @if($staff->isEmpty())
            <div style="text-align:center;padding:4rem;background:#fff;border-radius:16px;border:1px dashed #cbd5e1;">
                <p style="color:#94a3b8;font-size:1rem;font-weight:600;">No staff members have been added yet.</p>
            </div>
        @else
            <div class="staff-grid">
                @foreach($staff as $member)
                    @php
                        $badgeClass = match($member->availability) {
                            'Available' => 'badge-available',
                            'Busy' => 'badge-busy',
                            default => 'badge-off'
                        };
                        $initials = strtoupper(substr($member->name, 0, 1));
                    @endphp
                    <div class="staff-card">
                        <div class="staff-avatar">{{ $initials }}</div>
                        <div class="staff-name">{{ $member->name }}</div>
                        <div class="staff-role">{{ $member->role }}</div>
                        @if($member->notes)
                            <div style="font-size:.8rem; color:#94a3b8; margin-bottom:.75rem; font-style:italic;">{{ $member->notes }}</div>
                        @endif
                        <div class="staff-footer">
                            <span class="badge {{ $badgeClass }}">{{ $member->availability }}</span>
                            <form action="{{ route('admin.manpower.update_status') }}" method="POST" class="quick-status-form">
                                @csrf
                                <input type="hidden" name="crew_id" value="{{ $member->id }}">
                                <select name="availability" class="quick-status-select">
                                    <option value="Available" {{ $member->availability == 'Available' ? 'selected' : '' }}>Available</option>
                                    <option value="Busy" {{ $member->availability == 'Busy' ? 'selected' : '' }}>Busy</option>
                                    <option value="Off Duty" {{ $member->availability == 'Off Duty' ? 'selected' : '' }}>Off Duty</option>
                                </select>
                                <button type="submit" class="btn-update">Set</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
