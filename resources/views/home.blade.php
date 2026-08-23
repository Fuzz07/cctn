@extends('layouts.app')

@push('styles')
<style>
    /* ═══════════ BCTVI Blue Landing Theme ═══════════ */
    :root {
        --bl-blue: #dc2626;
        --bl-blue-dark: #b91c1c;
        --bl-navy: #0f172a;
        --bl-slate: #64748b;
        --bl-sky-bg: #fef2f2;
        --bl-sky-soft: #fee2e2;
    }

    /* ── Hero ── */
    .bl-hero {
        background: #fff;
        overflow: hidden;
    }
    .bl-hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 3.5rem 1.5rem 2.5rem;
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 2.5rem;
        align-items: center;
    }
    .bl-badge {
        display: inline-flex;
        align-items: center;
        background: var(--bl-sky-soft);
        color: var(--bl-blue);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 0.45rem 1.1rem;
        border-radius: 50px;
        margin-bottom: 1.5rem;
    }
    .bl-hero-title {
        font-family: var(--font-heading, inherit);
        font-size: clamp(2.1rem, 5.5vw, 3.4rem);
        font-weight: 800;
        line-height: 1.12;
        color: var(--bl-navy);
        letter-spacing: -0.02em;
        margin-bottom: 1.25rem;
    }
    .bl-hero-title .bl-accent { color: var(--bl-blue); }
    .bl-hero-sub {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 470px;
        margin-bottom: 2rem;
    }
    .bl-hero-btns { display: flex; gap: 0.9rem; flex-wrap: wrap; }
    .bl-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bl-blue);
        color: #fff;
        text-decoration: none;
        padding: 0.85rem 1.75rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.98rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.28);
        transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
    }
    .bl-btn-primary:hover {
        background: var(--bl-blue-dark);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(220, 38, 38, 0.38);
    }
    .bl-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #fff;
        color: var(--bl-navy);
        text-decoration: none;
        padding: 0.85rem 1.6rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.98rem;
        border: 1.5px solid #d1d5db;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
    }
    .bl-btn-outline:hover {
        border-color: var(--bl-blue);
        color: var(--bl-blue);
        background: var(--bl-sky-bg);
    }
    .bl-hero-visual { display: flex; justify-content: center; align-items: center; }
    .bl-hero-visual svg { width: 100%; max-width: 560px; height: auto; display: block; }

    /* ── Feature Bar ── */
    .bl-features-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem 3rem;
        position: relative;
    }
    .bl-features-bar {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 10px 35px rgba(15, 23, 42, 0.07);
        padding: 1.6rem 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }
    .bl-feature-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 0.25rem 1rem;
    }
    .bl-feature-item + .bl-feature-item { border-left: 1px solid #f1f5f9; }
    .bl-feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--bl-blue);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }
    .bl-feature-icon i { font-size: 1.35rem; line-height: 1; }
    .bl-check i { font-size: 0.85rem; line-height: 1; }
    .bl-info-card h3 i { color: var(--bl-blue); font-size: 1.3rem; }
    .bl-feature-item strong { display: block; font-size: 1rem; font-weight: 800; color: var(--bl-navy); }
    .bl-feature-item span { font-size: 0.85rem; color: var(--bl-slate); }

    /* ── Plans ── */
    .bl-plans-section { background: #fff; padding: 3.5rem 0 5rem; }
    .bl-container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
    .bl-section-label {
        text-align: center;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--bl-blue);
        margin-bottom: 0.6rem;
    }
    .bl-section-title {
        text-align: center;
        font-family: var(--font-heading, inherit);
        font-size: 2.3rem;
        font-weight: 800;
        color: var(--bl-navy);
        letter-spacing: -0.01em;
        margin-bottom: 2.5rem;
    }
    .bl-plans-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    .bl-plan-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 2.25rem 2rem;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
    }
    .bl-plan-card:hover {
        transform: translateY(-5px);
        border-color: #f5b5b5;
        box-shadow: 0 14px 40px rgba(220, 38, 38, 0.13);
    }
    .bl-plan-badge {
        align-self: flex-start;
        background: var(--bl-blue);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.3rem 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .bl-plan-name {
        font-family: var(--font-heading, inherit);
        font-size: clamp(1.35rem, 2vw, 1.7rem);
        font-weight: 800;
        color: var(--bl-navy);
        margin-bottom: 0.85rem;
    }
    .bl-plan-price {
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        margin-bottom: 1.25rem;
    }
    .bl-plan-amount {
        font-family: var(--font-heading, inherit);
        font-size: 2.3rem;
        font-weight: 800;
        color: var(--bl-blue);
    }
    .bl-plan-period { font-size: 0.95rem; color: var(--bl-slate); font-weight: 500; }
    .bl-plan-features {
        list-style: none;
        margin: 0 0 1.75rem;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        flex: 1;
    }
    .bl-plan-features li {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.98rem;
        color: #334155;
        font-weight: 500;
    }
    .bl-check {
        width: 21px;
        height: 21px;
        border-radius: 50%;
        background: var(--bl-blue);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bl-plan-btn {
        display: block;
        text-align: center;
        text-decoration: none;
        background: #fff;
        color: var(--bl-blue);
        border: 1.5px solid var(--bl-blue);
        font-weight: 700;
        font-size: 1rem;
        padding: 0.85rem;
        border-radius: 9px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .bl-plan-btn:hover { background: var(--bl-blue); color: #fff; }
    .bl-no-plans { grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--bl-slate); }

    /* ── App Download / Support / About ── */
    .bl-download-section { background: #fff; padding: 4rem 0; }
    .bl-download-inner {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 2.5rem;
        align-items: center;
        background: #fff;
        border-radius: 20px;
        padding: 2.5rem 3rem;
        box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
    }
    .bl-left-label { text-align: left; }
    .bl-left-title { text-align: left; margin-bottom: 1rem; font-size: 1.8rem; }
    .bl-download-sub { color: #475569; line-height: 1.7; font-size: 0.98rem; margin-bottom: 1.5rem; }

    .bl-info-section { background: #fff; padding: 4rem 0; }
    .bl-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4rem; }
    .bl-info-card {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 2rem;
    }
    .bl-info-card h3 {
        font-family: var(--font-heading, inherit);
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--bl-navy);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .bl-info-card p { color: #475569; font-size: 0.93rem; line-height: 1.7; margin: 0; }
    .bl-info-phone {
        display: inline-block;
        margin-top: 1rem;
        font-family: var(--font-heading, inherit);
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--bl-blue);
    }

    /* ── Status banner (logged-in clients) ── */
    .bl-status-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #fff;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* ── Responsive ── */
    @media (max-width: 1000px) {
        .bl-plans-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 860px) {
        .bl-hero-inner { grid-template-columns: 1fr; text-align: center; padding-top: 2.5rem; }
        .bl-hero-sub { margin-left: auto; margin-right: auto; }
        .bl-hero-btns { justify-content: center; }
        .bl-hero-visual { max-width: 440px; margin: 0 auto; }
        .bl-features-bar { grid-template-columns: 1fr; gap: 1rem; }
        .bl-feature-item { justify-content: flex-start; }
        .bl-feature-item + .bl-feature-item { border-left: none; border-top: 1px solid #f1f5f9; padding-top: 1rem; }
        .bl-download-inner { grid-template-columns: 1fr; text-align: center; padding: 2rem 1.5rem; }
        .bl-left-label, .bl-left-title { text-align: center; }
        .bl-info-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .bl-plans-grid { grid-template-columns: 1fr; }
        .bl-section-title { font-size: 1.8rem; }
        .bl-hero-inner { padding: 2rem 1.1rem 2rem; gap: 1.75rem; }
        .bl-container, .bl-features-wrap { padding-left: 1.1rem; padding-right: 1.1rem; }
        .bl-plan-card { padding: 1.75rem 1.5rem; }
        .bl-download-inner { padding: 1.75rem 1.25rem; }
        .bl-info-card { padding: 1.5rem 1.25rem; }
        .bl-hero-btns .bl-btn-primary,
        .bl-hero-btns .bl-btn-outline { flex: 1 1 auto; justify-content: center; }
    }
</style>
@endpush

@section('content')
<!-- ========== HERO ========== -->
<section class="bl-hero">
    <div class="bl-hero-inner">
        <!-- Left: Copy -->
        <div>
            <div class="bl-badge">Bantayan Island</div>
            <h1 class="bl-hero-title">
                Reliable Internet<br>
                for <span class="bl-accent">Your Home</span>
            </h1>
            <p class="bl-hero-sub">
                Enjoy fast, unlimited fiber broadband across Bantayan Island.
                Manage bookings, monitor technical installation schedules, and
                view billing statements seamlessly from your phone.
            </p>
            <div class="bl-hero-btns">
                @auth('client')
                    <a href="{{ route('client.book') }}" class="bl-btn-primary" id="cta-book">
                        Book Installation
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('client.appointments') }}" class="bl-btn-outline" id="cta-dash">
                        View My Bookings
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bl-btn-primary" id="cta-get-started">
                        Get Started
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#plans" class="bl-btn-outline" id="cta-view-plans">
                        View Plans
                        <i class="bi bi-wifi"></i>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Right: House Illustration -->
        <div class="bl-hero-visual">
            <svg viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="House with WiFi connection illustration">
                <!-- Blob background -->
                <path d="M323 34 C 462 18, 588 82, 610 200 C 630 314, 566 420, 408 448 C 250 476, 78 434, 44 316 C 12 202, 148 54, 323 34 Z" fill="#fee2e2"/>
                <!-- Clouds -->
                <g fill="#ffffff">
                    <ellipse cx="112" cy="150" rx="42" ry="18"/>
                    <ellipse cx="138" cy="138" rx="30" ry="16"/>
                    <ellipse cx="530" cy="120" rx="52" ry="20"/>
                    <ellipse cx="560" cy="106" rx="34" ry="17"/>
                    <ellipse cx="495" cy="112" rx="28" ry="14"/>
                </g>
                <!-- WiFi signal -->
                <g stroke="#dc2626" stroke-width="11" stroke-linecap="round" fill="none">
                    <path d="M276 118 a 62 62 0 0 1 88 0"/>
                    <path d="M296 142 a 34 34 0 0 1 48 0"/>
                </g>
                <circle cx="320" cy="162" r="8" fill="#dc2626"/>
                <!-- Ground shadow -->
                <rect x="130" y="404" width="390" height="12" rx="6" fill="#fca5a5"/>
                <!-- House body -->
                <rect x="188" y="264" width="264" height="141" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                <!-- Roof -->
                <path d="M152 268 L 320 176 L 488 268 Z" fill="#7f1d1d" stroke="#7f1d1d" stroke-width="14" stroke-linejoin="round"/>
                <!-- Door -->
                <path d="M297 405 v-64 a 23 23 0 0 1 46 0 v64 Z" fill="#991b1b"/>
                <circle cx="333" cy="360" r="3" fill="#fca5a5"/>
                <!-- Windows -->
                <g>
                    <rect x="212" y="296" width="58" height="56" rx="5" fill="#fecaca" stroke="#991b1b" stroke-width="5"/>
                    <line x1="241" y1="299" x2="241" y2="349" stroke="#991b1b" stroke-width="4"/>
                    <rect x="370" y="296" width="58" height="56" rx="5" fill="#fecaca" stroke="#991b1b" stroke-width="5"/>
                    <line x1="399" y1="299" x2="399" y2="349" stroke="#991b1b" stroke-width="4"/>
                </g>
                <!-- Left plant -->
                <g>
                    <path d="M142 404 C 140 380, 128 366, 110 358 M142 404 C 144 376, 156 362, 172 354 M142 404 C 141 386, 138 372, 140 356" stroke="#16a34a" stroke-width="5" fill="none" stroke-linecap="round"/>
                    <ellipse cx="106" cy="354" rx="13" ry="7" fill="#22c55e" transform="rotate(-28 106 354)"/>
                    <ellipse cx="176" cy="350" rx="13" ry="7" fill="#22c55e" transform="rotate(24 176 350)"/>
                    <ellipse cx="140" cy="348" rx="8" ry="13" fill="#22c55e"/>
                </g>
                <!-- Right bushes -->
                <g>
                    <ellipse cx="500" cy="392" rx="30" ry="22" fill="#16a34a"/>
                    <ellipse cx="536" cy="398" rx="24" ry="17" fill="#22c55e"/>
                    <ellipse cx="472" cy="400" rx="18" ry="13" fill="#22c55e"/>
                </g>
                <!-- Small left bush -->
                <ellipse cx="176" cy="398" rx="16" ry="11" fill="#22c55e"/>
            </svg>
        </div>
    </div>

    <!-- Logged-in client: latest booking status -->
    @auth('client')
        @php
            $activeAppt = \App\Models\Appointment::with('service')
                ->where('client_id', auth('client')->id())
                ->orderBy('created_at', 'desc')
                ->first();
        @endphp
        @if ($activeAppt)
            <div class="bl-features-wrap" style="padding-bottom: 1rem;">
                <div class="bl-status-banner">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em;">Your Latest Booking Status</div>
                        <div style="font-size: 1.1rem; font-weight: 800; margin-top: 2px;">
                            {{ $activeAppt->service->service_name ?? 'WiFi Plan' }} &bull; Ref: {{ $activeAppt->booking_ref ?? ('#'.str_pad($activeAppt->id, 5, '0', STR_PAD_LEFT)) }}
                        </div>
                        <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 4px;">
                            Scheduled: <strong>{{ date('M d, Y', strtotime($activeAppt->preferred_date)) }} at {{ date('h:i A', strtotime($activeAppt->preferred_time)) }}</strong>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <span style="background: {{ $activeAppt->status === 'approved' ? '#16a34a' : ($activeAppt->status === 'cancelled' ? '#dc2626' : '#ea580c') }}; padding: 0.4rem 1rem; font-size: 0.85rem; border-radius: 50px; font-weight: 700;">
                            Status: {{ ucfirst($activeAppt->status) }}
                        </span>
                        <a href="{{ route('client.appointments') }}" class="bl-btn-outline" style="padding: 0.45rem 1rem; font-size: 0.85rem;">Details &rarr;</a>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Feature Bar -->
    <div class="bl-features-wrap">
        <div class="bl-features-bar">
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div>
                    <strong>High-Speed Fiber</strong>
                    <span>Up to 150 Mbps</span>
                </div>
            </div>
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <div>
                    <strong>Fast Installation</strong>
                    <span>Pick date &amp; time</span>
                </div>
            </div>
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <strong>Reliable Support</strong>
                    <span>24/7 Local Support</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== PLANS ========== -->
<section class="bl-plans-section" id="plans">
    <div class="bl-container">
        <div class="bl-section-label">Choose Your Plan</div>
        <h2 class="bl-section-title">BCTVI Internet Plans</h2>

        <div class="bl-plans-grid">
            @forelse ($services as $service)
                @php
                    $displayName = preg_replace('/\s*Plan\s*$/i', '', $service->service_name);
                    preg_match('/(\d+)\s*Mbps/i', $service->service_name . ' ' . ($service->speed ?? ''), $m);
                    $mbps = $m[1] ?? null;
                @endphp
                <div class="bl-plan-card" id="plan-{{ $service->id }}">
                    <span class="bl-plan-badge">Fiber Fast</span>
                    <div class="bl-plan-name">{{ $displayName }}</div>
                    <div class="bl-plan-price">
                        <span class="bl-plan-amount">₱{{ number_format($service->price, 0) }}</span>
                        <span class="bl-plan-period">/ month</span>
                    </div>
                    <ul class="bl-plan-features">
                        <li>
                            <span class="bl-check"><i class="bi bi-check-lg"></i></span>
                            {{ $mbps ? "Up to {$mbps} Mbps" : ($service->speed ?? 'High-Speed Fiber') }}
                        </li>
                        <li>
                            <span class="bl-check"><i class="bi bi-check-lg"></i></span>
                            Unlimited Data
                        </li>
                        <li>
                            <span class="bl-check"><i class="bi bi-check-lg"></i></span>
                            Free Installation
                        </li>
                        <li>
                            <span class="bl-check"><i class="bi bi-check-lg"></i></span>
                            24/7 Support
                        </li>
                    </ul>
                    @auth('client')
                        <a href="{{ route('client.book', ['service_id' => $service->id]) }}" class="bl-plan-btn" id="book-plan-{{ $service->id }}">Select Plan</a>
                    @else
                        <a href="{{ route('register') }}" class="bl-plan-btn" id="book-guest-{{ $service->id }}">Select Plan</a>
                    @endauth
                </div>
            @empty
                <div class="bl-no-plans">
                    <p>No active plans available at the moment. Please contact the BCTVI office.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ========== MOBILE APP DOWNLOAD ========== -->
<section class="bl-download-section" id="download">
    <div class="bl-container">
        <div class="bl-download-inner">
            <div>
                <div class="bl-section-label bl-left-label">Client App</div>
                <h2 class="bl-section-title bl-left-title">BCTVI Client Companion App</h2>
                <p class="bl-download-sub">
                    Book WiFi installation, monitor technician schedules, view monthly
                    statements, and receive real-time updates directly on your Android phone.
                </p>
                <a href="{{ route('download.apk') }}" class="bl-btn-primary">
                    <i class="bi bi-download"></i>
                    Download Official Android APK
                </a>
            </div>
            <div style="text-align: center;">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Mobile App" style="max-height: 200px; object-fit: contain;">
            </div>
        </div>
    </div>
</section>

<!-- ========== SUPPORT & ABOUT ========== -->
<section class="bl-info-section" id="support">
    <div class="bl-container">
        <div class="bl-info-grid">
            <div class="bl-info-card">
                <h3>
                    <i class="bi bi-headset"></i>
                    24/7 Customer Support
                </h3>
                <p>
                    Our local support team on Bantayan Island is ready to help with connection
                    issues, billing questions, and installation schedules — any time, any day.
                </p>
                <span class="bl-info-phone">0999 998 8209</span>
            </div>
            <div class="bl-info-card" id="about">
                <h3>
                    <i class="bi bi-info-circle"></i>
                    About BCTVI
                </h3>
                <p>
                    BCTVI Broadband Telecommunications delivers reliable, unlimited fiber
                    internet to homes and businesses across Bantayan Island — with fast
                    installation, transparent billing, and support from a team that lives
                    right in your community.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
