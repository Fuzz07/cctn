@extends('layouts.admin')

@section('title', 'Manage Services - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }

    .content-grid { display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; }
    @media(max-width:1024px){ .content-grid { grid-template-columns: 1fr; } }

    .form-card { background:#fff; border-radius:16px; padding:2rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); }
    .form-card-title { font-size:1.1rem; font-weight:800; color:#0f172a; margin:0 0 1.5rem; display:flex; align-items:center; gap:.5rem; }

    .form-group { margin-bottom:1.25rem; }
    .form-label { display:block; font-size:.85rem; font-weight:700; color:#334155; margin-bottom:.5rem; }
    .form-control { width:100%; padding:.75rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; box-sizing:border-box; font-family:inherit; }
    .form-control:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
    textarea.form-control { resize:vertical; }
    select.form-control { background:#fff; }
    
    .btn-submit { width:100%; background:#dc2626; color:#fff; padding:.85rem; border-radius:8px; font-weight:700; font-size:1rem; border:none; cursor:pointer; margin-top:.5rem; display:flex; justify-content:center; align-items:center; gap:.5rem; transition:all .2s; }
    .btn-submit:hover { background:#b91c1c; transform:translateY(-1px); }
    .btn-secondary-submit { background:#f1f5f9; color:#64748b; }
    .btn-secondary-submit:hover { background:#e2e8f0; color:#0f172a; transform:none; }

    .services-list { display:flex; flex-direction:column; gap:1rem; }
    .service-item { background:#fff; border-radius:12px; padding:1.25rem 1.5rem; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; transition:all .2s; }
    .service-item:hover { border-color:#cbd5e1; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
    .service-name { font-size:1rem; font-weight:700; color:#0f172a; margin:0 0 .35rem; }
    .service-meta { font-size:.82rem; color:#64748b; font-weight:500; }
    .service-desc { font-size:.82rem; color:#64748b; margin-top:.35rem; font-style:italic; }

    .badge-active { background:#dcfce7; color:#15803d; padding:.25rem .65rem; border-radius:20px; font-size:.72rem; font-weight:700; }
    .badge-inactive { background:#f1f5f9; color:#64748b; padding:.25rem .65rem; border-radius:20px; font-size:.72rem; font-weight:700; }

    .action-links { display:flex; gap:.5rem; align-items:center; flex-shrink:0; }
    .btn-action { padding:.4rem .85rem; border-radius:6px; font-size:.8rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:.25rem; border:1px solid transparent; cursor:pointer; background:none; }
    .btn-edit { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
    .btn-edit:hover { background:#dbeafe; }
    .btn-del { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
    .btn-del:hover { background:#fee2e2; }

    .edit-mode-banner { background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:.75rem 1rem; margin-bottom:1.25rem; font-size:.85rem; color:#d97706; font-weight:600; display:flex; justify-content:space-between; align-items:center; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Service Packages</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif

<div class="content-grid">
    <!-- Form Card -->
    <div>
        <div class="form-card">
            @if($editService)
                <div class="edit-mode-banner">
                    <span>✏️ Editing: <strong>{{ $editService->service_name }}</strong></span>
                    <a href="{{ route('admin.services') }}" style="font-size:.8rem; color:#dc2626; text-decoration:none; font-weight:700;">✕ Cancel Edit</a>
                </div>
            @endif
            <h3 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                {{ $editService ? 'Edit Service' : 'Add New Service' }}
            </h3>

            <form action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                @if($editService)
                    <input type="hidden" name="service_id" value="{{ $editService->id }}">
                @endif

                <div class="form-group">
                    <label class="form-label">Service Name *</label>
                    <input type="text" name="service_name" class="form-control" value="{{ old('service_name', $editService->service_name ?? '') }}" required placeholder="e.g. Basic Fiber Installation">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this service...">{{ old('description', $editService->description ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (minutes) *</label>
                    <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $editService->duration_minutes ?? '') }}" required min="1" placeholder="e.g. 120">
                </div>
                <div class="form-group">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $editService->price ?? '') }}" required min="0" placeholder="e.g. 2500.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="Active" {{ old('status', $editService->status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $editService->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $editService ? 'Update Service' : 'Create Service' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Services List -->
    <div>
        @if($services->isEmpty())
            <div style="text-align:center;padding:4rem;background:#fff;border-radius:16px;border:1px dashed #cbd5e1;">
                <p style="color:#94a3b8;font-size:1rem;font-weight:600;">No services have been created yet.</p>
            </div>
        @else
            <div class="services-list">
                @foreach($services as $svc)
                    <div class="service-item">
                        <div style="flex:1;">
                            <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.35rem;">
                                <p class="service-name" style="margin:0;">{{ $svc->service_name }}</p>
                                <span class="badge-{{ strtolower($svc->status) == 'active' ? 'active' : 'inactive' }}">{{ $svc->status }}</span>
                            </div>
                            <div class="service-meta">
                                ₱{{ number_format($svc->price, 2) }} &middot; ~{{ $svc->duration_minutes }} minutes
                            </div>
                            @if($svc->description)
                                <div class="service-desc">{{ $svc->description }}</div>
                            @endif
                        </div>
                        <div class="action-links">
                            <a href="{{ route('admin.services', ['edit_id' => $svc->id]) }}" class="btn-action btn-edit">Edit</a>
                            <form action="{{ route('admin.services.destroy', $svc->id) }}" method="POST" onsubmit="return confirm('Delete this service? This cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-del">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
