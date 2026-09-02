@extends('layouts.app')

@push('styles')
<style>
    /* ═══════════ BCTVI Red & White Theme ═══════════ */
    :root {
        --bl-red: #dc2626;
        --bl-red-dark: #b91c1c;
        --bl-navy: #0f172a;
        --bl-slate: #64748b;
        --bl-pink-bg: #fff5f5;
        --bl-pink-soft: #fee2e2;
    }

    /* ── Hero Section ── */
    .bl-hero {
        background: #ffffff;
        position: relative;
        overflow: hidden;
        padding-bottom: 2rem;
    }
    .bl-hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 3.5rem 1.5rem 2rem;
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 2.5rem;
        align-items: center;
    }
    .bl-badge {
        display: inline-flex;
        align-items: center;
        background: var(--bl-pink-soft);
        color: var(--bl-red);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 0.45rem 1.1rem;
        border-radius: 50px;
        margin-bottom: 1.25rem;
    }
    .bl-hero-title {
        font-family: var(--font-heading, inherit);
        font-size: clamp(2.2rem, 5.5vw, 3.4rem);
        font-weight: 900;
        line-height: 1.12;
        color: var(--bl-navy);
        letter-spacing: -0.02em;
        margin-bottom: 1.25rem;
    }
    .bl-hero-title .bl-accent {
        color: var(--bl-red);
    }
    .bl-hero-sub {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 480px;
        margin-bottom: 2rem;
    }
    .bl-hero-btns {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .bl-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bl-red);
        color: #ffffff !important;
        text-decoration: none;
        padding: 0.85rem 1.85rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.98rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.28);
        transition: all 0.25s ease;
    }
    .bl-btn-primary:hover {
        background: var(--bl-red-dark);
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(220, 38, 38, 0.38);
    }
    .bl-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        color: var(--bl-red) !important;
        text-decoration: none;
        padding: 0.85rem 1.75rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.98rem;
        border: 1.5px solid var(--bl-red);
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .bl-btn-outline:hover {
        border-color: var(--bl-red-dark);
        color: var(--bl-red-dark) !important;
        background: var(--bl-pink-bg);
        transform: translateY(-2px);
    }
    .bl-hero-visual {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .bl-hero-visual svg {
        width: 100%;
        max-width: 540px;
        height: auto;
        display: block;
    }

    /* ── Feature Bar (3 Pills Container) ── */
    .bl-features-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 1.5rem;
        position: relative;
        z-index: 10;
    }
    .bl-features-bar {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        padding: 1.5rem 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }
    .bl-feature-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.1rem;
        padding: 0.25rem 1.25rem;
    }
    .bl-feature-item + .bl-feature-item {
        border-left: 1px solid #f1f5f9;
    }
    .bl-feature-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--bl-red);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
    }
    .bl-feature-icon i {
        font-size: 1.35rem;
        line-height: 1;
    }
    .bl-feature-item strong {
        display: block;
        font-size: 1.02rem;
        font-weight: 800;
        color: var(--bl-navy);
    }
    .bl-feature-item span {
        font-size: 0.88rem;
        color: var(--bl-slate);
    }

    /* ── Plans Section ── */
    .bl-plans-section {
        background: #ffffff;
        padding: 4.5rem 0 6rem;
        position: relative;
        overflow: hidden;
    }
    .bl-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
        position: relative;
        z-index: 2;
    }
    .bl-section-label {
        text-align: center;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--bl-red);
        margin-bottom: 0.5rem;
    }
    .bl-section-title {
        text-align: center;
        font-family: var(--font-heading, inherit);
        font-size: 2.3rem;
        font-weight: 900;
        color: var(--bl-navy);
        letter-spacing: -0.01em;
        margin-bottom: 0.5rem;
    }
    .bl-section-sub {
        text-align: center;
        color: var(--bl-slate);
        font-size: 1rem;
        margin-bottom: 3rem;
    }
    .bl-plans-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem;
        align-items: stretch;
    }
    .bl-plan-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2.25rem 2rem;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
    }
    .bl-plan-card:hover {
        transform: translateY(-5px);
        border-color: #fca5a5;
        box-shadow: 0 14px 40px rgba(220, 38, 38, 0.12);
    }
    .bl-plan-card.bl-popular-card {
        border: 2px solid var(--bl-red);
        box-shadow: 0 10px 35px rgba(220, 38, 38, 0.14);
    }
    .bl-popular-banner {
        position: absolute;
        top: 0;
        right: 0;
        background: var(--bl-red);
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        padding: 0.35rem 1rem;
        border-radius: 0 18px 0 12px;
        text-transform: uppercase;
    }
    .bl-plan-badge {
        align-self: flex-start;
        background: var(--bl-red);
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.35rem 0.95rem;
        border-radius: 50px;
        margin-bottom: 1.25rem;
    }
    .bl-plan-name {
        font-family: var(--font-heading, inherit);
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--bl-navy);
        margin-bottom: 0.5rem;
    }
    .bl-plan-price {
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }
    .bl-plan-amount {
        font-family: var(--font-heading, inherit);
        font-size: 2.4rem;
        font-weight: 900;
        color: var(--bl-navy);
    }
    .bl-plan-period {
        font-size: 0.95rem;
        color: var(--bl-slate);
        font-weight: 600;
    }
    .bl-plan-speed {
        font-size: 0.92rem;
        color: var(--bl-slate);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .bl-plan-divider {
        height: 1px;
        background: #f1f5f9;
        margin-bottom: 1.5rem;
    }
    .bl-plan-features {
        list-style: none;
        margin: 0 0 2rem;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        flex: 1;
    }
    .bl-plan-features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        color: #334155;
        font-weight: 600;
    }
    .bl-plan-features li i {
        color: var(--bl-red);
        font-size: 1.1rem;
        line-height: 1;
    }
    .bl-plan-btn {
        display: block;
        text-align: center;
        background: var(--bl-red);
        color: #ffffff !important;
        text-decoration: none;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .bl-plan-btn:hover {
        background: var(--bl-red-dark);
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    /* Decorative bottom-left wave */
    .bl-wave-bg {
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 320px;
        height: auto;
        opacity: 0.7;
        pointer-events: none;
        z-index: 1;
    }

    /* ── App Download Section ── */
    .bl-download-section {
        background: var(--bl-pink-bg);
        padding: 4rem 0;
        border-top: 1px solid #fee2e2;
    }
    .bl-download-inner {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 2.5rem;
        align-items: center;
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
        .bl-features-bar { grid-template-columns: 1fr; gap: 1.25rem; }
        .bl-feature-item { justify-content: flex-start; }
        .bl-feature-item + .bl-feature-item { border-left: none; border-top: 1px solid #f1f5f9; padding-top: 1.25rem; }
        .bl-download-inner { grid-template-columns: 1fr; text-align: center; }
    }
    @media (max-width: 560px) {
        .bl-plans-grid { grid-template-columns: 1fr; }
        .bl-section-title { font-size: 1.8rem; }
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
                Manage bookings, monitor installation schedules, and
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

        <!-- Right: House & Router Illustration -->
        <div class="bl-hero-visual">
            <svg viewBox="0 0 600 440" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="House with fiber router illustration">
                <defs>
                    <linearGradient id="pinkArchGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#fee2e2" stop-opacity="0.8"/>
                        <stop offset="100%" stop-color="#fecaca" stop-opacity="0.6"/>
                    </linearGradient>
                </defs>

                <!-- Arch Background Dome -->
                <path d="M 120 440 V 220 C 120 70, 480 70, 480 220 V 440 Z" fill="url(#pinkArchGrad)"/>

                <!-- Red Background Trees/Bushes -->
                <g fill="#dc2626">
                    <circle cx="470" cy="330" r="48" fill="#b91c1c"/>
                    <circle cx="515" cy="350" r="35" fill="#dc2626"/>
                    <circle cx="435" cy="360" r="32" fill="#ef4444"/>
                    <circle cx="160" cy="380" r="28" fill="#ef4444"/>
                    <circle cx="130" cy="390" r="22" fill="#dc2626"/>
                </g>

                <!-- Ground Shadow -->
                <ellipse cx="300" cy="425" rx="260" ry="12" fill="#fca5a5" opacity="0.6"/>

                <!-- Modern House -->
                <!-- House Main Wall -->
                <rect x="220" y="240" width="250" height="175" rx="4" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                <!-- House Roof -->
                <path d="M 190 245 L 345 140 L 500 245 Z" fill="#b91c1c" stroke="#991b1b" stroke-width="12" stroke-linejoin="round"/>
                <path d="M 220 245 L 345 160 L 470 245 Z" fill="#991b1b"/>
                
                <!-- Balcony Railing / Upper Windows -->
                <rect x="260" y="255" width="70" height="45" fill="#ffffff" stroke="#dc2626" stroke-width="3" rx="2"/>
                <line x1="295" y1="255" x2="295" y2="300" stroke="#dc2626" stroke-width="2"/>
                
                <rect x="365" y="255" width="70" height="45" fill="#ffffff" stroke="#dc2626" stroke-width="3" rx="2"/>
                <line x1="400" y1="255" x2="400" y2="300" stroke="#dc2626" stroke-width="2"/>

                <!-- Lower Large Windows & Door -->
                <rect x="260" y="330" width="75" height="75" fill="#ffffff" stroke="#dc2626" stroke-width="4" rx="3"/>
                <line x1="297" y1="330" x2="297" y2="405" stroke="#dc2626" stroke-width="2"/>
                <line x1="260" y1="367" x2="335" y2="367" stroke="#dc2626" stroke-width="2"/>

                <rect x="365" y="330" width="75" height="75" fill="#ffffff" stroke="#dc2626" stroke-width="4" rx="3"/>
                <line x1="402" y1="330" x2="402" y2="405" stroke="#dc2626" stroke-width="2"/>
                <line x1="365" y1="367" x2="440" y2="367" stroke="#dc2626" stroke-width="2"/>

                <!-- Fiber Router / Modem in Foreground -->
                <!-- WiFi Signal Waves above Router -->
                <g stroke="#dc2626" stroke-linecap="round" fill="none">
                    <path d="M 270 240 A 50 50 0 0 1 330 240" stroke-width="9"/>
                    <path d="M 282 260 A 30 30 0 0 1 318 260" stroke-width="7"/>
                    <circle cx="300" cy="278" r="6" fill="#dc2626" stroke="none"/>
                </g>

                <!-- White Router Body -->
                <rect x="210" y="310" width="180" height="85" rx="16" fill="#ffffff" stroke="#e2e8f0" stroke-width="3"/>
                <!-- Router Top Antenna/Status Bar -->
                <circle cx="280" cy="335" r="7" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="2"/>
                <circle cx="300" cy="335" r="5" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="2"/>

                <!-- Green LED Indicators -->
                <g fill="#22c55e">
                    <circle cx="260" cy="370" r="3"/>
                    <circle cx="272" cy="370" r="3"/>
                    <circle cx="284" cy="370" r="3"/>
                    <circle cx="296" cy="370" r="3"/>
                    <circle cx="308" cy="370" r="3"/>
                </g>
            </svg>
        </div>
    </div>

    <!-- Feature Bar -->
    <div class="bl-features-wrap">
        <div class="bl-features-bar">
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                </div>
                <div>
                    <strong>High-Speed Fiber</strong>
                    <span>Up to 150 Mbps</span>
                </div>
            </div>
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-clock-fill"></i>
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
                    <strong>Built-in Chatbot</strong>
                    <span>Mobile and web help</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== PLANS ========== -->
<section class="bl-plans-section" id="plans">
    <!-- Soft Red Wave Graphic Accent -->
    <svg class="bl-wave-bg" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M-50 120 C 80 250, 220 180, 350 300 L -50 300 Z" fill="#fee2e2"/>
    </svg>

    <div class="bl-container">
        <div class="bl-section-label">CHOOSE YOUR PLAN</div>
        <h2 class="bl-section-title">BCTVI Internet Plans</h2>
        <p class="bl-section-sub">Affordable plans for every home and lifestyle.</p>

        <div class="bl-plans-grid">
            @forelse ($services as $index => $service)
                @php
                    $displayName = preg_replace('/\s*Plan\s*$/i', '', $service->service_name);
                    preg_match('/(\d+)\s*Mbps/i', $service->service_name . ' ' . ($service->speed ?? ''), $m);
                    $mbps = $m[1] ?? null;

                    // Hardcode or detect popular plan (e.g. middle plan or Fiber Plus)
                    $isPopular = ($index === 1) || str_contains(strtolower($service->service_name), 'plus');
                    
                    // Assign default badges if not specified
                    $badgeName = 'FIBER STARTER';
                    if (str_contains(strtolower($service->service_name), 'plus') || $index === 1) {
                        $badgeName = 'FIBER PLUS';
                    } elseif (str_contains(strtolower($service->service_name), 'max') || $index >= 2) {
                        $badgeName = 'FIBER MAX';
                    }
                @endphp
                
                <div class="bl-plan-card {{ $isPopular ? 'bl-popular-card' : '' }}" id="plan-{{ $service->id }}">
                    @if ($isPopular)
                        <div class="bl-popular-banner">MOST POPULAR</div>
                    @endif

                    <span class="bl-plan-badge">{{ $badgeName }}</span>
                    
                    <div class="bl-plan-price">
                        <span class="bl-plan-amount">₱{{ number_format($service->price, 0) }}</span>
                        <span class="bl-plan-period">/month</span>
                    </div>
                    
                    <div class="bl-plan-speed">
                        {{ $mbps ? "Up to {$mbps} Mbps" : ($service->speed ?? 'High-Speed Fiber') }}
                    </div>

                    <div class="bl-plan-divider"></div>

                    <ul class="bl-plan-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Unlimited Internet
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Free Installation
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            24/7 Customer Support
                        </li>
                    </ul>

                    @auth('client')
                        <a href="{{ route('client.book', ['service_id' => $service->id]) }}" class="bl-plan-btn" id="book-plan-{{ $service->id }}">Select Plan</a>
                    @else
                        <a href="{{ route('register') }}" class="bl-plan-btn" id="book-guest-{{ $service->id }}">Select Plan</a>
                    @endauth
                </div>
            @empty
                <!-- Fallback static plan cards matching exact attached reference if DB empty -->
                <div class="bl-plan-card">
                    <span class="bl-plan-badge">FIBER STARTER</span>
                    <div class="bl-plan-price">
                        <span class="bl-plan-amount">₱599</span>
                        <span class="bl-plan-period">/month</span>
                    </div>
                    <div class="bl-plan-speed">Up to 25 Mbps</div>
                    <div class="bl-plan-divider"></div>
                    <ul class="bl-plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited Internet</li>
                        <li><i class="bi bi-check-circle-fill"></i> Free Installation</li>
                        <li><i class="bi bi-check-circle-fill"></i> 24/7 Customer Support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="bl-plan-btn">Select Plan</a>
                </div>

                <div class="bl-plan-card bl-popular-card">
                    <div class="bl-popular-banner">MOST POPULAR</div>
                    <span class="bl-plan-badge">FIBER PLUS</span>
                    <div class="bl-plan-price">
                        <span class="bl-plan-amount">₱999</span>
                        <span class="bl-plan-period">/month</span>
                    </div>
                    <div class="bl-plan-speed">Up to 50 Mbps</div>
                    <div class="bl-plan-divider"></div>
                    <ul class="bl-plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited Internet</li>
                        <li><i class="bi bi-check-circle-fill"></i> Free Installation</li>
                        <li><i class="bi bi-check-circle-fill"></i> 24/7 Customer Support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="bl-plan-btn">Select Plan</a>
                </div>

                <div class="bl-plan-card">
                    <span class="bl-plan-badge">FIBER MAX</span>
                    <div class="bl-plan-price">
                        <span class="bl-plan-amount">₱1,499</span>
                        <span class="bl-plan-period">/month</span>
                    </div>
                    <div class="bl-plan-speed">Up to 150 Mbps</div>
                    <div class="bl-plan-divider"></div>
                    <ul class="bl-plan-features">
                        <li><i class="bi bi-check-circle-fill"></i> Unlimited Internet</li>
                        <li><i class="bi bi-check-circle-fill"></i> Free Installation</li>
                        <li><i class="bi bi-check-circle-fill"></i> 24/7 Customer Support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="bl-plan-btn">Select Plan</a>
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
                <div class="bl-section-label" style="text-align: left;">CLIENT APP</div>
                <h2 class="bl-section-title" style="text-align: left;">BCTVI Client Companion App</h2>
                <p style="color: #475569; font-size: 1rem; line-height: 1.7; margin-bottom: 1.5rem;">
                    Book WiFi installation, monitor technician schedules, view monthly
                    statements, chat with the built-in BCTVI Assistant, and receive
                    real-time updates directly on your Android phone.
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
<section style="background: #ffffff; padding: 4rem 0;" id="support">
    <div class="bl-container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--bl-navy); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-headset" style="color: var(--bl-red);"></i>
                    24/7 Customer Support
                </h3>
                <p style="color: #475569; font-size: 0.95rem; line-height: 1.7; margin-bottom: 1rem;">
                    Our local support team on Bantayan Island is ready to help with connection
                    issues, billing questions, and installation schedules.
                </p>
                <span style="font-size: 1.3rem; font-weight: 900; color: var(--bl-red);">0999 998 8209</span>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem;" id="about">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--bl-navy); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-info-circle" style="color: var(--bl-red);"></i>
                    About BCTVI
                </h3>
                <p style="color: #475569; font-size: 0.95rem; line-height: 1.7;">
                    BCTVI Broadband Telecommunications delivers reliable, unlimited fiber
                    internet to homes and businesses across Bantayan Island — with fast
                    installation, transparent billing, and local support.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
