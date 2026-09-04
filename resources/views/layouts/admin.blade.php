<!DOCTYPE html>
<html lang="en" class="admin-app {{ request()->routeIs('admin.dashboard') ? 'admin-dashboard-page' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#dc2626">
    <title>@yield('title', 'Admin Panel - BCTVI Bantayan')</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    @stack('styles')
</head>
<body class="admin-app {{ request()->routeIs('admin.dashboard') ? 'admin-dashboard-page' : '' }}">

    <div class="admin-dashboard-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div>
                <div class="admin-sidebar-brand">
                    <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo" style="width: 38px; height: 38px; object-fit: contain;">
                    <div>
                        <strong style="color: #ffffff; font-family: var(--font-heading, sans-serif); font-size: 1.25rem; font-weight: 800; display: block; line-height: 1;">BCTVI</strong>
                        <span style="color: rgba(255,255,255,0.85); font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em;">Bantayan</span>
                    </div>
                </div>

                <div class="admin-sidebar-menu">
                    <div>
                        <div class="admin-sidebar-category">MAIN MENU</div>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                                Dashboard
                            </a>
                            <a href="{{ route('admin.appointments') }}" class="admin-sidebar-item {{ request()->routeIs('admin.appointments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                Bookings
                            </a>
                            <a href="{{ route('admin.walkin.create') }}" class="admin-sidebar-item {{ request()->routeIs('admin.walkin*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                Walk-In Booking
                            </a>
                            <a href="{{ route('admin.clients') }}" class="admin-sidebar-item {{ request()->routeIs('admin.clients*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                Customers
                            </a>
                            <a href="{{ route('admin.services') }}" class="admin-sidebar-item {{ request()->routeIs('admin.services*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                Plans &amp; Services
                            </a>
                            <a href="{{ route('admin.billing') }}" class="admin-sidebar-item {{ request()->routeIs('admin.billing*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                Payments
                            </a>
                            <a href="{{ route('admin.sales') }}" class="admin-sidebar-item {{ request()->routeIs('admin.sales*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                Sales Revenue
                            </a>
                            <a href="{{ route('admin.maintenance') }}" class="admin-sidebar-item {{ request()->routeIs('admin.maintenance*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                Installations
                            </a>
                            <a href="{{ route('admin.schedules') }}" class="admin-sidebar-item {{ request()->routeIs('admin.schedules*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Time Schedules
                            </a>
                            <a href="{{ route('admin.equipment') }}" class="admin-sidebar-item {{ request()->routeIs('admin.equipment*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect></svg>
                                Equipments
                            </a>
                            <a href="{{ route('admin.manpower') }}" class="admin-sidebar-item {{ request()->routeIs('admin.manpower*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                Man Power
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="admin-sidebar-category">SYSTEM</div>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="admin-sidebar-item" style="color: #ef4444; width:100%; border:none; background:none; text-align:left; cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="admin-main-wrapper">
            <header class="admin-topbar">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button id="admin-sidebar-toggle" style="background: none; border: none; color: #0f172a; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                </div>
                
                @php
                    $unread_count = \App\Models\Notification::adminUnread()->count();
                    $notifs = \App\Models\Notification::forAdmin()->get();
                @endphp

                <div style="display: flex; align-items: center; gap: 1.25rem; position: relative;">
                    <!-- Notification Bell -->
                    <div style="position: relative;" id="notif-wrapper">
                        <button type="button" id="notif-bell-btn" style="background: none; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex; align-items: center; justify-content: center; position: relative;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            @if ($unread_count > 0)
                                <span id="notif-badge" style="position: absolute; top: -2px; right: -2px; background: #dc2626; color: #fff; font-size: 0.6rem; font-weight: 800; min-width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; padding: 0 2px;">{{ $unread_count }}</span>
                            @endif
                        </button>

                        <div id="notif-dropdown" style="display: none; position: absolute; right: 0; top: 42px; width: 340px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 12px 30px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                            <div style="padding: 0.85rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                                <strong style="font-size: 0.88rem; color: #0f172a;">Notifications</strong>
                                <button type="button" id="mark-all-read-btn" style="background: none; border: none; color: #dc2626; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Mark all read</button>
                            </div>
                            <div style="max-height: 320px; overflow-y: auto;">
                                @forelse ($notifs as $notif)
                                    <a href="{{ url($notif->link ?? 'admin/dashboard') }}" class="notif-item-link" data-id="{{ $notif->id }}" style="display: block; padding: 0.85rem 1rem; border-bottom: 1px solid #f1f5f9; text-decoration: none; background: {{ $notif->is_read ? '#ffffff' : '#fef2f2' }}; transition: background 0.15s;">
                                        <strong style="font-size: 0.82rem; color: #0f172a; display: block; margin-bottom: 0.15rem;">{{ $notif->title }}</strong>
                                        <p style="font-size: 0.78rem; color: #64748b; margin: 0 0 0.25rem 0; line-height: 1.3;">{{ $notif->message }}</p>
                                        <span style="font-size: 0.68rem; color: #94a3b8; font-weight: 500;">{{ $notif->created_at->format('M d, g:i A') }}</span>
                                    </a>
                                @empty
                                    <div style="padding: 2rem; text-align: center; color: #94a3b8; font-size: 0.82rem;">No notifications found</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.8rem; padding-left: 0.5rem; border-left: 1px solid #e2e8f0;">
                        <div style="line-height: 1.2;">
                            <strong style="font-size: 0.85rem; color: #0f172a; display: block; font-weight: 700;">{{ auth('admin')->user()->fullname }}</strong>
                            <span style="font-size: 0.72rem; color: #64748b; font-weight: 500;">{{ auth('admin')->user()->role == 'super_admin' ? 'Administrator' : 'Sub Administrator' }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content-pane {{ request()->routeIs('admin.dashboard') ? 'admin-dashboard-pane' : '' }}">
                @if (session('success_message'))
                    <div class="alert alert-success fade-in">{{ session('success_message') }}</div>
                @endif
                @if (session('error_message'))
                    <div class="alert alert-danger fade-in">{{ session('error_message') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger fade-in">
                        <ul style="margin:0; padding-left:1rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggleBtn = document.getElementById('admin-sidebar-toggle');
            var sidebar = document.querySelector('.admin-sidebar');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                });
            }

            var bellBtn = document.getElementById('notif-bell-btn');
            var dropdown = document.getElementById('notif-dropdown');
            var markAllBtn = document.getElementById('mark-all-read-btn');
            var badge = document.getElementById('notif-badge');

            if (bellBtn && dropdown) {
                bellBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.style.display = (dropdown.style.display === 'none' || !dropdown.style.display) ? 'block' : 'none';
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            }

            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var formData = new FormData();
                    formData.append('mark_all_admin', '1');
                    formData.append('_token', '{{ csrf_token() }}');
                    fetch('{{ route("admin.notifications.read") }}', { method: 'POST', body: formData })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.success) {
                                if (badge) badge.style.display = 'none';
                                document.querySelectorAll('.notif-item-link').forEach(function(el) {
                                    el.style.background = '#ffffff';
                                });
                            }
                        });
                });
            }

            document.querySelectorAll('.notif-item-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var notifId = this.getAttribute('data-id');
                    var targetUrl = this.getAttribute('href');
                    
                    if (this.style.background === 'rgb(255, 255, 255)' || this.style.backgroundColor === 'white') {
                        return;
                    }
                    
                    e.preventDefault();
                    var self = this;
                    
                    var formData = new FormData();
                    formData.append('notification_id', notifId);
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    fetch('{{ route("admin.notifications.read") }}', { method: 'POST', body: formData })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            self.style.background = '#ffffff';
                        }
                        window.location.href = targetUrl;
                    })
                    .catch(function() {
                        window.location.href = targetUrl;
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
