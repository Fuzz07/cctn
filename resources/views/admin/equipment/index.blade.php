@extends('layouts.admin')

@section('title', 'Equipment Inventory - BCTVI Bantayan')

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

    .eq-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1rem; }
    .eq-card { background:#fff; border-radius:14px; padding:1.25rem 1.5rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); transition:all .2s; }
    .eq-card:hover { border-color:#cbd5e1; box-shadow:0 6px 20px rgba(0,0,0,0.06); }
    .eq-cat { font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin-bottom:.5rem; }
    .eq-name { font-size:1rem; font-weight:700; color:#0f172a; margin-bottom:.5rem; }
    .eq-meta { display:flex; justify-content:space-between; align-items:center; }
    .eq-qty { font-size:.85rem; font-weight:600; color:#475569; }
    .badge { padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; }
    .badge-good { background:#dcfce7; color:#15803d; }
    .badge-low { background:#fef3c7; color:#d97706; }
    .badge-depleted { background:#fee2e2; color:#dc2626; }
    .badge-maintenance { background:#eff6ff; color:#2563eb; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Equipment Inventory</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif

<div class="content-grid">
    <!-- Add Equipment Form -->
    <div>
        <div class="form-card">
            <h3 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Add Equipment Item
            </h3>
            <form action="{{ route('admin.equipment.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Item Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Fiber Optic Router">
                </div>
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" required placeholder="e.g. Networking, Tools, Cables">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="Good">Good</option>
                        <option value="Low Stock">Low Stock</option>
                        <option value="Depleted">Depleted</option>
                        <option value="For Maintenance">For Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional remarks...">
                </div>
                <button type="submit" class="btn-submit">Add to Inventory</button>
            </form>
        </div>
    </div>

    <!-- Equipment Cards -->
    <div>
        @if($equipment->isEmpty())
            <div style="text-align:center;padding:4rem;background:#fff;border-radius:16px;border:1px dashed #cbd5e1;">
                <p style="color:#94a3b8;font-size:1rem;font-weight:600;">No equipment has been added yet.</p>
            </div>
        @else
            <div class="eq-grid">
                @foreach($equipment as $item)
                    @php
                        $badgeClass = match(strtolower($item->status)) {
                            'good' => 'badge-good',
                            'low stock' => 'badge-low',
                            'depleted' => 'badge-depleted',
                            default => 'badge-maintenance'
                        };
                    @endphp
                    <div class="eq-card">
                        <div class="eq-cat">{{ $item->category }}</div>
                        <div class="eq-name">{{ $item->name }}</div>
                        @if($item->notes)
                            <div style="font-size:.8rem; color:#94a3b8; margin-bottom:.75rem; font-style:italic;">{{ $item->notes }}</div>
                        @endif
                        <div class="eq-meta">
                            <span class="eq-qty">Qty: {{ $item->quantity }}</span>
                            <span class="badge {{ $badgeClass }}">{{ $item->status }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
