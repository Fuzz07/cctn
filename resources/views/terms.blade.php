@extends('layouts.app')

@section('title', 'Terms and Conditions - BCTVI Bantayan')

@section('content')
<style>
    .terms-page-wrap {
        padding: 3rem 0 5rem;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }
    .terms-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        padding: 3.5rem 0 3rem;
        position: relative;
        overflow: hidden;
        border-bottom: 3px solid #dc2626;
    }
    .terms-header::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(220,38,38,0.18) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .terms-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(220,38,38,0.2);
        border: 1px solid rgba(220,38,38,0.4);
        color: #fca5a5;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.4rem 0.9rem;
        border-radius: 99px;
        margin-bottom: 1rem;
    }
    .terms-title {
        font-family: var(--font-heading);
        font-size: 2.4rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 0.8rem;
        line-height: 1.15;
    }
    .terms-subtitle {
        color: #cbd5e1;
        font-size: 1.05rem;
        max-width: 650px;
        line-height: 1.6;
    }
    .terms-meta-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-top: 1.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 0.85rem;
        color: #94a3b8;
    }
    .terms-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .terms-meta-item i {
        color: #dc2626;
    }

    .terms-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2.5rem;
        margin-top: 2.5rem;
        align-items: flex-start;
    }

    /* Sticky Sidebar Navigation */
    .terms-sidebar {
        position: sticky;
        top: 90px;
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(15,23,42,0.04);
        padding: 1.5rem;
    }
    .terms-sidebar-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .terms-toc {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .terms-toc-link {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    .terms-toc-link:hover, .terms-toc-link.active {
        background: #fef2f2;
        color: #dc2626;
    }
    .terms-toc-num {
        font-size: 0.75rem;
        font-weight: 800;
        color: #94a3b8;
        width: 18px;
    }
    .terms-toc-link:hover .terms-toc-num, .terms-toc-link.active .terms-toc-num {
        color: #dc2626;
    }

    /* Content Cards */
    .terms-content {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }
    .terms-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(15,23,42,0.03);
        padding: 2.25rem 2.5rem;
        transition: border-color 0.2s;
    }
    .terms-card:hover {
        border-color: #cbd5e1;
    }
    .terms-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .terms-card-num {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .terms-card-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .terms-card-body {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.75;
    }
    .terms-card-body p {
        margin-bottom: 1rem;
    }
    .terms-card-body p:last-child {
        margin-bottom: 0;
    }
    .terms-card-body ul, .terms-card-body ol {
        margin: 0.8rem 0 1rem 1.4rem;
        padding: 0;
    }
    .terms-card-body li {
        margin-bottom: 0.5rem;
    }
    .terms-card-body strong {
        color: #0f172a;
    }

    .terms-highlight-box {
        background: #f8fafc;
        border-left: 4px solid #dc2626;
        border-radius: 0 10px 10px 0;
        padding: 1rem 1.25rem;
        margin: 1.25rem 0;
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    /* Action bar */
    .terms-action-bar {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15,23,42,0.06);
        padding: 2rem 2.5rem;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    .terms-action-info h4 {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.35rem 0;
    }
    .terms-action-info p {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }
    .terms-btn-group {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .btn-terms-primary {
        background: #dc2626;
        color: #ffffff;
        padding: 0.85rem 1.75rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(220,38,38,0.25);
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-terms-primary:hover {
        background: #b91c1c;
        color: #ffffff;
        transform: translateY(-1px);
    }
    .btn-terms-secondary {
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 0.85rem 1.4rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-terms-secondary:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    @media (max-width: 991px) {
        .terms-layout {
            grid-template-columns: 1fr;
        }
        .terms-sidebar {
            display: none;
        }
    }
    @media (max-width: 640px) {
        .terms-title { font-size: 1.8rem; }
        .terms-card { padding: 1.5rem 1.25rem; }
        .terms-action-bar { padding: 1.5rem; flex-direction: column; align-items: stretch; text-align: center; }
        .terms-btn-group { flex-direction: column; width: 100%; }
        .btn-terms-primary, .btn-terms-secondary { width: 100%; justify-content: center; }
    }

    @media print {
        .site-header, .site-footer, .terms-sidebar, .terms-action-bar, .bottom-bar { display: none !important; }
        .terms-page-wrap { padding: 0; background: #fff; }
        .terms-header { background: #fff; color: #000; border: none; padding: 1rem 0; }
        .terms-title { color: #000; font-size: 1.8rem; }
        .terms-subtitle { color: #555; }
        .terms-card { border: 1px solid #ccc; box-shadow: none; break-inside: avoid; page-break-inside: avoid; margin-bottom: 1.5rem; }
    }
</style>

<!-- Hero Section -->
<section class="terms-header">
    <div class="bl-container">
        <div class="terms-badge">
            <i class="bi bi-shield-check"></i> Legal Agreement &amp; Client Policies
        </div>
        <h1 class="terms-title">Client Terms and Conditions</h1>
        <p class="terms-subtitle">
            Welcome to BCTVI Broadband Telecommunications. These Terms and Conditions govern your client account,
            Fiber WiFi subscriptions, installation appointments, and access to our online portal on Bantayan Island.
        </p>
        <div class="terms-meta-bar">
            <div class="terms-meta-item">
                <i class="bi bi-calendar3"></i> Last Updated: September 2026
            </div>
            <div class="terms-meta-item">
                <i class="bi bi-geo-alt"></i> Coverage: Bantayan, Santa Fe, &amp; Madridejos, Cebu
            </div>
            <div class="terms-meta-item">
                <i class="bi bi-telephone"></i> Helpline: 0999 998 8209
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<div class="terms-page-wrap">
    <div class="bl-container">
        <div class="terms-layout">
            
            <!-- Sticky Quick Navigation -->
            <aside class="terms-sidebar">
                <div class="terms-sidebar-title">
                    <i class="bi bi-list-nested" style="color: #dc2626;"></i> Table of Contents
                </div>
                <ul class="terms-toc">
                    <li><a href="#section-1" class="terms-toc-link"><span class="terms-toc-num">01</span> Overview &amp; Acceptance</a></li>
                    <li><a href="#section-2" class="terms-toc-link"><span class="terms-toc-num">02</span> Client Account &amp; Login</a></li>
                    <li><a href="#section-3" class="terms-toc-link"><span class="terms-toc-num">03</span> Fiber WiFi &amp; Installation</a></li>
                    <li><a href="#section-4" class="terms-toc-link"><span class="terms-toc-num">04</span> Billing &amp; Due Dates</a></li>
                    <li><a href="#section-5" class="terms-toc-link"><span class="terms-toc-num">05</span> Payment Channels</a></li>
                    <li><a href="#section-6" class="terms-toc-link"><span class="terms-toc-num">06</span> Acceptable Use Policy</a></li>
                    <li><a href="#section-7" class="terms-toc-link"><span class="terms-toc-num">07</span> Customer Premises Equipment</a></li>
                    <li><a href="#section-8" class="terms-toc-link"><span class="terms-toc-num">08</span> Privacy &amp; Data Protection</a></li>
                    <li><a href="#section-9" class="terms-toc-link"><span class="terms-toc-num">09</span> Maintenance &amp; Outages</a></li>
                    <li><a href="#section-10" class="terms-toc-link"><span class="terms-toc-num">10</span> Support &amp; Disputes</a></li>
                </ul>
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('login') }}" class="btn-terms-primary" style="width: 100%; justify-content: center; font-size: 0.88rem; padding: 0.65rem 1rem;">
                        <i class="bi bi-box-arrow-in-right"></i> Go to Client Login
                    </a>
                </div>
            </aside>

            <!-- Main Legal Content -->
            <main class="terms-content">

                <!-- 01: Overview & Acceptance -->
                <article class="terms-card" id="section-1">
                    <div class="terms-card-header">
                        <div class="terms-card-num">01</div>
                        <h2 class="terms-card-title">Agreement Overview &amp; Acceptance</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            By signing in, accessing, or registering an account on the <strong>BCTVI Broadband Portal</strong>
                            (accessible via web and the official Android companion application), or by subscribing to BCTVI
                            fiber internet installation services, you ("Client", "Subscriber", or "User") agree to be bound
                            by these Terms and Conditions in full.
                        </p>
                        <p>
                            If you do not agree with any part of these terms, you must refrain from accessing the client portal
                            and discontinue any ongoing subscription or booking request. These terms form a legally binding
                            contract between you and <strong>BCTVI Broadband Telecommunications</strong>.
                        </p>
                        <div class="terms-highlight-box">
                            <strong>Notice for Existing Subscribers:</strong> Continuing to log into the client portal to
                            view statements, pay monthly bills, schedule maintenance, or book appointments constitutes ongoing
                            acceptance of these updated terms and any subsequent revisions.
                        </div>
                    </div>
                </article>

                <!-- 02: Client Account & Login -->
                <article class="terms-card" id="section-2">
                    <div class="terms-card-header">
                        <div class="terms-card-num">02</div>
                        <h2 class="terms-card-title">Client Account, Login &amp; Security</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>To access the client dashboard and broadband services, clients must authenticate using valid credentials:</p>
                        <ul>
                            <li><strong>Account Credentials:</strong> You are responsible for safeguarding your username, registered email address, and account password. Do not share your sign-in details with unauthorized persons.</li>
                            <li><strong>Accurate Information:</strong> You agree to provide true, accurate, current, and complete registration details, including your full legal name, active Philippine mobile number, installation address, and verified proof of billing.</li>
                            <li><strong>Unauthorized Access:</strong> You must promptly notify BCTVI Customer Support if you suspect any security breach, lost device, or unauthorized login to your client portal.</li>
                            <li><strong>Session Security:</strong> Always sign out from shared or public computers after completing your booking, payment verification, or dashboard activities.</li>
                        </ul>
                    </div>
                </article>

                <!-- 03: Fiber WiFi & Installation -->
                <article class="terms-card" id="section-3">
                    <div class="terms-card-header">
                        <div class="terms-card-num">03</div>
                        <h2 class="terms-card-title">Fiber WiFi Subscriptions &amp; Installation</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            BCTVI provides unlimited broadband fiber internet services across covered service areas in Bantayan Island,
                            encompassing the municipalities of <strong>Bantayan, Santa Fe, and Madridejos</strong>.
                        </p>
                        <ul>
                            <li><strong>Site Feasibility:</strong> All online booking requests are subject to physical facility availability, optical distribution point (ODP) terminal capacity, and fiber drop cable distance to your premises.</li>
                            <li><strong>Premises Access:</strong> The client authorizes certified BCTVI technicians to enter the designated premises at the agreed appointment schedule to conduct site surveys, string optical fiber cables, install network terminal equipment, and activate service.</li>
                            <li><strong>Appointment Rescheduling:</strong> Rescheduling of installation appointments must be requested at least 24 hours prior to the scheduled installation slot through the client portal or by contacting customer support.</li>
                        </ul>
                    </div>
                </article>

                <!-- 04: Billing & Due Dates -->
                <article class="terms-card" id="section-4">
                    <div class="terms-card-header">
                        <div class="terms-card-num">04</div>
                        <h2 class="terms-card-title">Billing, Due Dates &amp; Disconnection Policy</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            Subscribers are billed on a fixed monthly cycle according to their chosen internet plan (e.g., Fiber Starter, Fiber Plus, Fiber Max):
                        </p>
                        <ul>
                            <li><strong>Monthly Invoices:</strong> Monthly statements of account (SOA) are generated automatically and made accessible on the client dashboard under <em>Payments &amp; Statements</em>.</li>
                            <li><strong>Due Dates:</strong> Payment must be settled on or before the due date specified on each billing invoice to maintain uninterrupted fiber connectivity.</li>
                            <li><strong>Grace Period &amp; Disconnection:</strong> Failure to settle outstanding balances within five (5) days following the due date may result in automated temporary service disconnection.</li>
                            <li><strong>Reconnection:</strong> Reconnection of disconnected lines requires full settlement of any overdue balances. Standard reconnection processing occurs within 2 to 24 hours after verified payment receipt.</li>
                        </ul>
                    </div>
                </article>

                <!-- 05: Payment Channels -->
                <article class="terms-card" id="section-5">
                    <div class="terms-card-header">
                        <div class="terms-card-num">05</div>
                        <h2 class="terms-card-title">Payment Channels &amp; Verification</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            BCTVI offers convenient online and on-site payment methods to ensure smooth transactions:
                        </p>
                        <ul>
                            <li><strong>Online E-Wallets &amp; Banking:</strong> Payments can be remitted via GCash, Maya, or bank transfer using the official BCTVI payment channels shown in the client portal.</li>
                            <li><strong>Proof of Payment Upload:</strong> For digital transfers, clients must upload a clear screenshot of the transaction receipt showing the reference number, amount, and timestamp for prompt validation.</li>
                            <li><strong>Walk-In Cash Payments:</strong> Cash payments are accepted at the official BCTVI Bantayan business office. Always request an official electronic or printed receipt.</li>
                        </ul>
                    </div>
                </article>

                <!-- 06: Acceptable Use Policy -->
                <article class="terms-card" id="section-6">
                    <div class="terms-card-header">
                        <div class="terms-card-num">06</div>
                        <h2 class="terms-card-title">Acceptable Use Policy (AUP)</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            The BCTVI fiber network is intended for legitimate residential and commercial usage. Users agree strictly NOT to use the service for:
                        </p>
                        <ul>
                            <li>Any unlawful, fraudulent, defamatory, harassing, or malicious activities violating Philippine and international laws.</li>
                            <li>Distributing computer viruses, malware, spam, port scanning, denial of service (DoS/DDoS) attacks, or attempting unauthorized access to remote servers.</li>
                            <li>Commercial redistribution or unlicensed reselling of the internet connection without prior written authorization from BCTVI management.</li>
                            <li>Operating public hotspot networks that compromise neighborhood security or degrade network node performance.</li>
                        </ul>
                    </div>
                </article>

                <!-- 07: Customer Premises Equipment -->
                <article class="terms-card" id="section-7">
                    <div class="terms-card-header">
                        <div class="terms-card-num">07</div>
                        <h2 class="terms-card-title">Equipment Ownership &amp; Maintenance</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            Network equipment supplied by BCTVI (including Optical Network Terminals / ONTs, fiber optic drop cables, and splitters) remains the property of BCTVI unless outright purchase terms apply:
                        </p>
                        <ul>
                            <li><strong>Care of Equipment:</strong> The subscriber agrees to exercise due care over the modem and indoor optical cables, protecting them against water damage, physical breakage, power surges, or unauthorized tampering.</li>
                            <li><strong>Repairs &amp; Troubleshooting:</strong> Clients may book troubleshooting appointments directly through the portal. Do not attempt to re-splice fiber optic drop wires independently, as high-power laser signals can cause eye injury.</li>
                            <li><strong>Equipment Retrieval:</strong> In the event of account termination or cancellation, supplied terminal modems and adapters must be returned in operable condition.</li>
                        </ul>
                    </div>
                </article>

                <!-- 08: Privacy & Data Protection -->
                <article class="terms-card" id="section-8">
                    <div class="terms-card-header">
                        <div class="terms-card-num">08</div>
                        <h2 class="terms-card-title">Privacy &amp; Data Protection (RA 10173)</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            In strict compliance with the <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong> of the Philippines,
                            BCTVI is committed to safeguarding client privacy and personal data:
                        </p>
                        <ul>
                            <li><strong>Collected Data:</strong> We collect your name, contact numbers, email address, physical barangay address, and verification proofs strictly for account administration, billing, installation logistics, and emergency network alerts.</li>
                            <li><strong>Confidentiality:</strong> Your personal information is never sold, traded, or shared with third-party marketers. Data is only accessible to authorized BCTVI technical, dispatch, and billing personnel.</li>
                            <li><strong>Data Retention &amp; Security:</strong> Digital records and uploaded receipts are encrypted and stored within secure systems with access logs and role-based permissions.</li>
                        </ul>
                    </div>
                </article>

                <!-- 09: Maintenance & Outages -->
                <article class="terms-card" id="section-9">
                    <div class="terms-card-header">
                        <div class="terms-card-num">09</div>
                        <h2 class="terms-card-title">Service Maintenance &amp; Limitation of Liability</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            While BCTVI aims for 99.5% network uptime, subscribers acknowledge that telecommunications services may experience periodic interruptions:
                        </p>
                        <ul>
                            <li><strong>Scheduled Maintenance:</strong> Preventive maintenance, optical backbone upgrades, and power supply calibrations will be announced in advance through portal alerts and SMS notices whenever feasible.</li>
                            <li><strong>Force Majeure:</strong> BCTVI is not liable for disruptions caused by natural disasters (typhoons, lightning strikes, earthquakes), utility pole damage by third-party vehicular accidents, island-wide electric power outages (BANELCO), or submarine cable cuts beyond local control.</li>
                            <li><strong>Limitation of Damages:</strong> BCTVI shall not be held liable for indirect, incidental, or consequential business losses arising from temporary service downtime.</li>
                        </ul>
                    </div>
                </article>

                <!-- 10: Support & Disputes -->
                <article class="terms-card" id="section-10">
                    <div class="terms-card-header">
                        <div class="terms-card-num">10</div>
                        <h2 class="terms-card-title">Customer Support, Disputes &amp; Inquiries</h2>
                    </div>
                    <div class="terms-card-body">
                        <p>
                            For inquiries, dispute resolution, or assistance regarding your connection or portal account, our local Bantayan Island support team is available through the following official channels:
                        </p>
                        <ul>
                            <li><strong>Hotline / Mobile:</strong> 0999 998 8209</li>
                            <li><strong>Business Office:</strong> BCTVI Broadband Telecommunications, Bantayan Island, Cebu, Philippines</li>
                            <li><strong>Online Chat:</strong> Interactive 24/7 AI and dispatcher assistant directly available on every portal page</li>
                            <li><strong>Governing Law:</strong> These terms are governed and construed in accordance with the laws of the Republic of the Philippines.</li>
                        </ul>
                    </div>
                </article>

                <!-- Bottom Action Banner -->
                <div class="terms-action-bar">
                    <div class="terms-action-info">
                        <h4>Ready to Access Your Client Account?</h4>
                        <p>Log in with your username or email to view statements, book services, and manage your fiber connection.</p>
                    </div>
                    <div class="terms-btn-group">
                        <button type="button" class="btn-terms-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Terms
                        </button>
                        <a href="{{ route('login') }}" class="btn-terms-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Proceed to Login
                        </a>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<script>
    // Smooth scroll and active link indicator for Table of Contents
    document.querySelectorAll('.terms-toc-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, null, targetId);
                document.querySelectorAll('.terms-toc-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Highlight current section on scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('.terms-card');
        const scrollPos = window.scrollY + 140;

        sections.forEach(section => {
            const top = section.offsetTop;
            const height = section.offsetHeight;
            const id = section.getAttribute('id');
            if (scrollPos >= top && scrollPos < top + height) {
                document.querySelectorAll('.terms-toc-link').forEach(link => {
                    link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                });
            }
        });
    });
</script>
@endsection
