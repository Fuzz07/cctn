@extends('layouts.admin')

@section('title', 'Promos - BCTVI Bantayan')

@push('styles')
<style>
    .promo-layout { display:grid; grid-template-columns:minmax(280px, 380px) minmax(0, 1fr); gap:1.5rem; align-items:start; }
    .promo-card, .promo-item { background:var(--bg-card); border:1px solid var(--border-light); border-radius:14px; padding:1.5rem; }
    .promo-title { margin:0 0 1rem; color:var(--text-dark); font-size:1.15rem; font-weight:800; }
    .promo-field { margin-bottom:1rem; }
    .promo-field label { display:block; margin-bottom:.4rem; color:var(--text-body); font-size:.84rem; font-weight:700; }
    .promo-field input, .promo-field textarea { width:100%; box-sizing:border-box; padding:.7rem .8rem; border:1px solid var(--border); border-radius:8px; font:inherit; background:var(--bg-card); color:var(--text-dark); }
    .promo-dates { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
    .promo-submit { width:100%; border:0; border-radius:8px; padding:.8rem; background:#dc2626; color:#fff; font-weight:800; cursor:pointer; }
    .promo-list { display:grid; gap:1rem; }
    .promo-item { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; }
    .promo-item h3 { margin:0 0 .35rem; color:var(--text-dark); font-size:1rem; }
    .promo-item p { margin:0 0 .6rem; color:var(--text-muted); font-size:.85rem; }
    .promo-meta { color:var(--text-muted); font-size:.78rem; }
    .promo-actions { display:flex; gap:.5rem; flex-shrink:0; }
    .promo-actions a, .promo-actions button { border:1px solid var(--border-light); border-radius:7px; padding:.4rem .65rem; background:var(--bg-card); color:var(--text-body); text-decoration:none; font-size:.78rem; font-weight:700; cursor:pointer; }
    .promo-status { display:inline-block; margin-bottom:.5rem; padding:.25rem .6rem; border-radius:99px; font-size:.72rem; font-weight:800; }
    @media(max-width:900px) { .promo-layout { grid-template-columns:1fr; } }
    @media(max-width:520px) { .promo-item { flex-direction:column; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Promos</h1>
</div>

@if(session('success_message'))
    <div role="status" style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:10px;margin-bottom:1.25rem;font-weight:600;">
        {{ session('success_message') }}
    </div>
@endif

@if($errors->any())
    <div role="alert" style="background:#fef2f2;color:#b91c1c;padding:1rem;border-radius:10px;margin-bottom:1.25rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="promo-layout">
    <section class="promo-card">
        <h2 class="promo-title">{{ $editPromo ? 'Edit Promo' : 'Create a Promo' }}</h2>
        <form action="{{ route('admin.promos.store') }}" method="POST">
            @csrf
            @if($editPromo)
                <input type="hidden" name="promo_id" value="{{ $editPromo->id }}">
            @endif
            <div class="promo-field">
                <label for="promo-title">Title</label>
                <input id="promo-title" name="title" maxlength="120" required value="{{ old('title', $editPromo->title ?? '') }}" placeholder="Christmas Promo">
            </div>
            <div class="promo-field">
                <label for="promo-description">Description</label>
                <textarea id="promo-description" name="description" rows="4" maxlength="1000" required placeholder="Describe the special offer">{{ old('description', $editPromo->description ?? '') }}</textarea>
            </div>
            <div class="promo-field">
                <label for="promo-discount">Discount (%)</label>
                <input id="promo-discount" type="number" name="discount_percent" min="0.01" max="100" step="0.01" required value="{{ old('discount_percent', $editPromo->discount_percent ?? '') }}">
            </div>
            <div class="promo-dates">
                <div class="promo-field">
                    <label for="promo-start">Start date</label>
                    <input id="promo-start" type="date" name="start_date" required value="{{ old('start_date', $editPromo?->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="promo-field">
                    <label for="promo-end">End date</label>
                    <input id="promo-end" type="date" name="end_date" required value="{{ old('end_date', $editPromo?->end_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <button type="submit" class="promo-submit">{{ $editPromo ? 'Save Promo' : 'Create Promo' }}</button>
            @if($editPromo)
                <a href="{{ route('admin.promos') }}" style="display:block;text-align:center;margin-top:.8rem;color:#dc2626;font-size:.85rem;font-weight:700;text-decoration:none;">Cancel editing</a>
            @endif
        </form>
    </section>

    <section class="promo-list" aria-label="All promos">
        @forelse($promos as $promo)
            @php
                $isActive = $promo->start_date->lte(today()) && $promo->end_date->gte(today());
                $isUpcoming = $promo->start_date->gt(today());
            @endphp
            <article class="promo-item">
                <div>
                    <span class="promo-status" style="background:{{ $isActive ? '#dcfce7' : ($isUpcoming ? '#dbeafe' : '#f1f5f9') }};color:{{ $isActive ? '#15803d' : ($isUpcoming ? '#1d4ed8' : '#64748b') }};">
                        {{ $isActive ? 'Active' : ($isUpcoming ? 'Scheduled' : 'Expired') }}
                    </span>
                    <h3>{{ $promo->title }} &mdash; {{ rtrim(rtrim(number_format($promo->discount_percent, 2), '0'), '.') }}% off</h3>
                    <p>{{ $promo->description }}</p>
                    <div class="promo-meta">{{ $promo->start_date->format('M d, Y') }} – {{ $promo->end_date->format('M d, Y') }}</div>
                </div>
                <div class="promo-actions">
                    <a href="{{ route('admin.promos', ['edit_id' => $promo->id]) }}">Edit</a>
                    <form action="{{ route('admin.promos.destroy', $promo) }}" method="POST" onsubmit="return confirm('Delete this promo? Existing bookings and receipts will keep their saved promo details.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="color:#b91c1c;">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="promo-card" style="color:var(--text-muted);">No promos have been created yet.</div>
        @endforelse
    </section>
</div>
@endsection
