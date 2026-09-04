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
        --bl-pink-border: #fecaca;
    }

    /* ── Hero Section ── */
    .bl-hero {
        background: #ffffff;
        position: relative;
        overflow: hidden;
        padding-top: 1.5rem;
        padding-bottom: 2rem;
    }

    /* Subtle background ambient waves */
    .bl-hero-bg-accent {
        position: absolute;
        top: 0;
        right: 0;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(254, 226, 226, 0.6) 0%, rgba(255, 245, 245, 0.2) 60%, transparent 100%);
        border-radius: 50%;
        filter: blur(40px);
        pointer-events: none;
        z-index: 0;
    }

    .bl-hero-bg-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: radial-gradient(ellipse 80% 50% at 50% 120%, rgba(254, 226, 226, 0.4), transparent);
        pointer-events: none;
        z-index: 0;
    }

    .bl-hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2.5rem 1.5rem 2rem;
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 3rem;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .bl-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--bl-pink-soft);
        color: var(--bl-red);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.45rem 1.15rem;
        border-radius: 50px;
        margin-bottom: 1.25rem;
    }

    .bl-hero-title {
        font-family: var(--font-heading, inherit);
        font-size: clamp(2.4rem, 5.2vw, 3.6rem);
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
        max-width: 490px;
        margin-bottom: 2.25rem;
    }

    .bl-hero-btns {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
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
        font-size: 1rem;
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
        font-size: 1rem;
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

    /* ── Realistic Dual Phone Mockup ── */
    .bl-mockup-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 480px;
    }

    .bl-mockup-glow {
        position: absolute;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, rgba(254, 202, 202, 0.85) 0%, rgba(254, 226, 226, 0.4) 60%, transparent 100%);
        border-radius: 50%;
        filter: blur(30px);
        z-index: 1;
    }

    /* Background Secondary Phone (Angled) */
    .bl-phone-bg {
        position: absolute;
        right: 15px;
        top: 40px;
        width: 220px;
        height: 400px;
        background: #ffffff;
        border-radius: 32px;
        border: 5px solid #1e293b;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
        z-index: 2;
        transform: rotate(6deg);
        overflow: hidden;
        padding: 12px;
        display: flex;
        flex-direction: column;
    }

    .bl-phone-bg-header {
        font-size: 0.75rem;
        font-weight: 800;
        color: #64748b;
        text-align: center;
        margin-top: 8px;
        margin-bottom: 12px;
    }

    .bl-phone-bg-badge {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 16px;
        padding: 16px 12px;
        text-align: center;
        margin-bottom: 12px;
    }

    .bl-phone-bg-badge-icon {
        width: 44px;
        height: 44px;
        background: #22c55e;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin: 0 auto 8px;
    }

    .bl-phone-bg-badge-title {
        font-size: 0.82rem;
        font-weight: 800;
        color: #15803d;
        display: block;
    }

    .bl-phone-bg-badge-sub {
        font-size: 0.68rem;
        color: #166534;
        font-weight: 600;
    }

    .bl-phone-bg-chart {
        flex: 1;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        padding: 8px;
        display: flex;
        align-items: flex-end;
    }

    /* Foreground Primary Phone */
    .bl-phone-fg {
        position: relative;
        width: 250px;
        height: 470px;
        background: #ffffff;
        border-radius: 36px;
        border: 6px solid #0f172a;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.22);
        z-index: 3;
        overflow: hidden;
        padding: 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .bl-phone-fg::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        width: 70px;
        height: 18px;
        background: #0f172a;
        border-radius: 12px;
        z-index: 10;
    }

    .bl-phone-status-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        color: #0f172a;
        padding: 0 4px;
        margin-top: 2px;
        margin-bottom: 8px;
    }

    .bl-phone-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-bottom: 10px;
    }

    .bl-phone-brand img {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }

    .bl-phone-brand strong {
        font-size: 0.85rem;
        font-weight: 900;
        color: #dc2626;
        line-height: 1;
    }

    .bl-phone-greeting {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 14px;
        padding: 10px 12px;
        color: #ffffff;
        margin-bottom: 12px;
    }

    .bl-phone-greeting strong {
        font-size: 0.82rem;
        font-weight: 800;
        display: block;
        line-height: 1.2;
    }

    .bl-phone-greeting span {
        font-size: 0.65rem;
        color: #cbd5e1;
    }

    .bl-phone-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }

    .bl-phone-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 6px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .bl-phone-tile-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .bl-tile-blue { background: #eff6ff; color: #2563eb; }
    .bl-tile-green { background: #f0fdf4; color: #16a34a; }
    .bl-tile-orange { background: #fff7ed; color: #ea580c; }
    .bl-tile-purple { background: #faf5ff; color: #9333ea; }

    .bl-phone-tile span {
        font-size: 0.68rem;
        font-weight: 700;
        color: #334155;
    }

    .bl-phone-btn {
        background: var(--bl-red);
        color: #ffffff;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 800;
        padding: 10px;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    /* ── Feature Highlights Bar (4 Columns) ── */
    .bl-features-wrap {
        max-width: 1140px;
        margin: 0 auto;
        padding: 0 1.5rem;
        position: relative;
        z-index: 10;
    }

    .bl-features-bar {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        padding: 1.5rem 1.25rem;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
    }

    .bl-feature-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 0.25rem 0.75rem;
    }

    .bl-feature-item + .bl-feature-item {
        border-left: 1px solid #f1f5f9;
    }

    .bl-feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--bl-red);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.28);
    }

    .bl-feature-icon i {
        font-size: 1.3rem;
        line-height: 1;
    }

    .bl-feature-item strong {
        display: block;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--bl-navy);
        line-height: 1.2;
    }

    .bl-feature-item span {
        font-size: 0.82rem;
        color: var(--bl-slate);
        font-weight: 500;
    }

    /* ── App Download Banner Card ── */
    .bl-app-banner-wrap {
        max-width: 1140px;
        margin: 1.5rem auto 0;
        padding: 0 1.5rem;
        position: relative;
        z-index: 10;
    }

    .bl-app-banner {
        background: var(--bl-pink-bg);
        border: 1.5px solid var(--bl-pink-border);
        border-radius: 20px;
        padding: 1.25rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.05);
    }

    .bl-app-banner-left {
        display: flex;
        align-items: center;
        gap: 1.1rem;
    }

    .bl-app-banner-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: var(--bl-red);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .bl-app-banner-text strong {
        display: block;
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--bl-navy);
        margin-bottom: 0.15rem;
    }

    .bl-app-banner-text span {
        font-size: 0.88rem;
        color: #475569;
        font-weight: 500;
    }

    .bl-app-banner-badges {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .bl-store-badge {
        display: inline-flex;
        align-items: center;
        background: #000000;
        color: #ffffff !important;
        text-decoration: none;
        padding: 0.45rem 0.95rem;
        border-radius: 8px;
        transition: transform 0.2s, opacity 0.2s;
    }

    .bl-store-badge:hover {
        transform: translateY(-2px);
        opacity: 0.92;
        color: #ffffff !important;
    }

    /* ── Plans Section ── */
    .bl-plans-section {
        background: #ffffff;
        padding: 4.5rem 0 5.5rem;
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

    /* ── Responsive ── */
    @media (max-width: 1024px) {
        .bl-features-bar { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .bl-feature-item + .bl-feature-item { border-left: none; }
        .bl-plans-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 860px) {
        .bl-hero-inner { grid-template-columns: 1fr; text-align: center; padding-top: 1.5rem; }
        .bl-hero-sub { margin-left: auto; margin-right: auto; }
        .bl-hero-btns { justify-content: center; }
        .bl-mockup-wrapper { min-height: 420px; }
        .bl-app-banner { flex-direction: column; text-align: center; }
        .bl-app-banner-left { flex-direction: column; }
    }

    @media (max-width: 640px) {
        .bl-features-bar { grid-template-columns: 1fr; gap: 1.25rem; }
        .bl-feature-item { justify-content: flex-start; }
        .bl-plans-grid { grid-template-columns: 1fr; }
        .bl-section-title { font-size: 1.85rem; }
        .bl-app-banner-badges { flex-direction: column; width: 100%; }
        .bl-store-badge { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
<!-- ========== HERO ========== -->
<section class="bl-hero">
    <div class="bl-hero-bg-accent"></div>
    <div class="bl-hero-bg-wave"></div>

    <div class="bl-hero-inner">
        <!-- Left: Copy & Actions -->
        <div>
            <div class="bl-badge">
                <i class="bi bi-wifi"></i>
                Bantayan Island
            </div>
            <h1 class="bl-hero-title">
                Reliable Internet<br>
                for <span class="bl-accent">Your Home</span>
            </h1>
            <p class="bl-hero-sub">
                Enjoy fast, unlimited fiber broadband across Bantayan Island.
                Manage bookings, monitor installation schedules, and view
                billing statements seamlessly from your phone or computer.
            </p>
            <div class="bl-hero-btns">
                @auth('client')
                    <a href="{{ route('client.book') }}" class="bl-btn-primary" id="cta-book">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Book Installation
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('client.appointments') }}" class="bl-btn-outline" id="cta-dash">
                        View My Bookings
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bl-btn-primary" id="cta-get-started">
                        <i class="bi bi-rocket-takeoff-fill"></i>
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

        <!-- Right: Modern Dual Phone App Mockup -->
        <div class="bl-mockup-wrapper">
            <div class="bl-mockup-glow"></div>

            <!-- Background Secondary Phone -->
            <div class="bl-phone-bg">
                <div class="bl-phone-bg-header">Connection</div>
                <div class="bl-phone-bg-badge">
                    <div class="bl-phone-bg-badge-icon">
                        <i class="bi bi-wifi"></i>
                    </div>
                    <span class="bl-phone-bg-badge-title">You're Connected!</span>
                    <span class="bl-phone-bg-badge-sub">Up to 150 Mbps</span>
                </div>
                <div class="bl-phone-bg-chart">
                    <svg viewBox="0 0 160 80" style="width: 100%; height: auto;" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 60 Q 30 30, 60 45 T 120 20 T 160 30" stroke="#22c55e" stroke-width="3" stroke-linecap="round"/>
                        <path d="M0 60 Q 30 30, 60 45 T 120 20 T 160 30 L 160 80 L 0 80 Z" fill="rgba(34, 197, 94, 0.15)"/>
                    </svg>
                </div>
            </div>

            <!-- Foreground Primary Phone -->
            <div class="bl-phone-fg">
                <div>
                    <div class="bl-phone-status-bar">
                        <span>9:41</span>
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <i class="bi bi-reception-4"></i>
                            <i class="bi bi-wifi"></i>
                            <i class="bi bi-battery-full"></i>
                        </div>
                    </div>

                    <div class="bl-phone-brand">
                        <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo">
                        <strong>BCTVI</strong>
                    </div>

                    <div class="bl-phone-greeting">
                        <strong>Hello,<br>Valued Customer!</strong>
                        <span>Manage your internet, anywhere, anytime.</span>
                    </div>

                    <div class="bl-phone-grid">
                        <div class="bl-phone-tile">
                            <div class="bl-phone-tile-icon bl-tile-blue"><i class="bi bi-person-fill"></i></div>
                            <span>My Account</span>
                        </div>
                        <div class="bl-phone-tile">
                            <div class="bl-phone-tile-icon bl-tile-green"><i class="bi bi-receipt"></i></div>
                            <span>Billing</span>
                        </div>
                        <div class="bl-phone-tile">
                            <div class="bl-phone-tile-icon bl-tile-orange"><i class="bi bi-geo-alt-fill"></i></div>
                            <span>Support</span>
                        </div>
                        <div class="bl-phone-tile">
                            <div class="bl-phone-tile-icon bl-tile-purple"><i class="bi bi-speedometer2"></i></div>
                            <span>Speed Test</span>
                        </div>
                    </div>
                </div>

                <div class="bl-phone-btn">
                    Open BCTVI App
                </div>
            </div>
        </div>
    </div>

    <!-- 4-Column Feature Bar -->
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
                    <i class="bi bi-calendar-event-fill"></i>
                </div>
                <div>
                    <strong>Fast Installation</strong>
                    <span>Pick date &amp; time</span>
                </div>
            </div>
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <div>
                    <strong>Secure &amp; Stable</strong>
                    <span>Reliable connection</span>
                </div>
            </div>
            <div class="bl-feature-item">
                <div class="bl-feature-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <strong>24/7 Support</strong>
                    <span>We're here to help</span>
                </div>
            </div>
        </div>
    </div>

    <!-- App Download Banner Card -->
    <div class="bl-app-banner-wrap" id="download">
        <div class="bl-app-banner">
            <div class="bl-app-banner-left">
                <div class="bl-app-banner-icon">
                    <i class="bi bi-wifi"></i>
                </div>
                <div class="bl-app-banner-text">
                    <strong>Download the BCTVI Mobile App</strong>
                    <span>Manage your account, check your bills, request support and more — all in one app.</span>
                </div>
            </div>
            <div class="bl-app-banner-badges">
                <!-- Google Play Badge -->
                <a href="{{ route('download.apk') }}" class="bl-store-badge" title="Get it on Google Play">
                    <svg viewBox="0 0 135 40" width="135" height="40" xmlns="http://www.w3.org/2000/svg">
                        <g fill="#ffffff">
                            <path d="M21.2 19.8l-7.4-7.4c-.2-.2-.5-.3-.8-.3-.4 0-.7.2-.9.5L12 12.8c-.1.2-.2.5-.2.8v12.8c0 .3.1.6.2.8l.1.2 7.7-7.4-4.6 4.6 6-4z" fill="#00e676" opacity=".2"/>
                            <!-- Play Triangle -->
                            <path d="M12.1 13.5l10.3 5.9-4.8 4.8-5.5-10.7z" fill="#00e676"/>
                            <path d="M22.4 19.4L12.1 25.3l5.5-10.7 4.8 4.8z" fill="#ff3d00"/>
                            <path d="M12.1 13.5v11.8l5.5-5.9-5.5-5.9z" fill="#ffc107"/>
                            <path d="M25.2 21l-2.8-1.6-4.8 4.8 4.8 4.8 2.8-1.6c.8-.5 1.3-1.4 1.3-2.4 0-1-.5-1.9-1.3-2.4z" fill="#2979ff"/>
                            <!-- Text: GET IT ON / Google Play -->
                            <text x="36" y="14" font-size="8" font-weight="600" fill="#ffffff" letter-spacing="0.5">GET IT ON</text>
                            <text x="36" y="27" font-size="13" font-weight="800" fill="#ffffff">Google Play</text>
                        </g>
                    </svg>
                </a>

                <!-- App Store Badge -->
                <a href="{{ route('download.apk') }}" class="bl-store-badge" title="Download on the App Store">
                    <svg viewBox="0 0 135 40" width="135" height="40" xmlns="http://www.w3.org/2000/svg">
                        <g fill="#ffffff">
                            <path d="M24.2 20.3c0-3.3 2.7-4.9 2.8-5-1.5-2.2-3.9-2.5-4.8-2.6-2-.2-4 1.2-5.1 1.2-1 0-2.6-1.2-4.3-1.2-2.2 0-4.3 1.3-5.4 3.3-2.3 4-0.6 10 1.6 13.3 1.1 1.6 2.4 3.3 4.1 3.2 1.7-.1 2.3-1.1 4.3-1.1 2 0 2.5 1.1 4.3 1.1 1.8 0 2.9-1.6 4-3.2 1.3-1.9 1.8-3.7 1.8-3.8-.1 0-3.4-1.3-3.4-5.2zM20.9 10.9c.9-1.1 1.5-2.6 1.3-4.1-1.3.1-2.8.9-3.7 1.9-.8.9-1.5 2.4-1.3 3.9 1.4.1 2.8-.6 3.7-1.7z" fill="#ffffff"/>
                            <text x="36" y="14" font-size="7.5" font-weight="600" fill="#ffffff" letter-spacing="0.4">Download on the</text>
                            <text x="36" y="27" font-size="13.5" font-weight="800" fill="#ffffff">App Store</text>
                        </g>
                    </svg>
                </a>
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

                    // Detect popular plan
                    $isPopular = ($index === 1) || str_contains(strtolower($service->service_name), 'plus');
                    
                    // Assign default badges
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
                <!-- Fallback Plan Cards -->
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
