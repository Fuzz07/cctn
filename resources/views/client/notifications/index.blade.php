@extends('layouts.app')

@section('title', 'My Notifications — CCTN / BCTVI Broadband')

@push('styles')
<style>
    .notif-container {
        max-width: 800px;
        margin: 1.5rem auto 3rem auto;
        padding: 0 1rem;
    }
    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .notif-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-read-all {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-read-all:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .notif-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        transition: all 0.2s ease;
        position: relative;
    }
    .notif-card.unread {
        border-left: 4px solid #dc2626;
        background: #fffdfd;
    }
    .notif-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .notif-body {
        flex: 1;
    }
    .notif-item-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .notif-message {
        font-size: 0.9rem;
        color: #475569;
        line-height: 1.5;
        margin-bottom: 0.5rem;
    }
    .notif-time {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94a3b8;
    }
    .notif-action {
        align-self: center;
    }
    .btn-mark-single {
        background: none;
        border: none;
        color: #dc2626;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
    }
    .btn-mark-single:hover {
        background: #fef2f2;
    }
    .empty-notif {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 3rem 1.5rem;
        text-align: center;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="notif-container">
    <div class="notif-header">
        <h1 class="notif-title">
            <i class="bi bi-bell"></i> Notifications
        </h1>
        @if ($notifications->where('is_read', false)->count() > 0)
            <form action="{{ route('client.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn-read-all">✓ Mark All as Read</button>
            </form>
        @endif
    </div>

    @if (session('success_message'))
        <div style="background:#dcfce7; color:#15803d; padding: 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-weight: 600; border: 1px solid #bbf7d0;">
            ✓ {{ session('success_message') }}
        </div>
    @endif

    <div class="notif-list">
        @forelse ($notifications as $notif)
            <div class="notif-card {{ $notif->is_read ? 'read' : 'unread' }}">
                <div class="notif-icon">
                    @if (Str::contains(strtolower($notif->title), ['payment', 'billing', 'paid']))
                        <i class="bi bi-credit-card"></i>
                    @elseif (Str::contains(strtolower($notif->title), ['installation', 'schedule', 'appointment']))
                        <i class="bi bi-calendar-event"></i>
                    @else
                        <i class="bi bi-megaphone"></i>
                    @endif
                </div>
                <div class="notif-body">
                    <div class="notif-item-title">{{ $notif->title }}</div>
                    <div class="notif-message">{{ $notif->message }}</div>
                    <div class="notif-time">{{ $notif->created_at->diffForHumans() }} &bull; {{ $notif->created_at->format('M d, Y h:i A') }}</div>
                </div>
                @if (!$notif->is_read)
                    <div class="notif-action">
                        <form action="{{ route('client.notifications.read', $notif->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mark-single">Mark Read</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-notif">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔕</div>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">No Notifications Yet</h3>
                <p style="margin: 0; font-size: 0.9rem;">You will receive real-time updates for booking confirmations, installation schedules, and payment receipts here.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
