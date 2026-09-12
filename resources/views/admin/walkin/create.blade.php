@extends('layouts.admin')

@section('title', 'CBTVI Walk-In Client Booking & Payment Form')

@push('styles')
<style>
    .walkin-container {
        max-width: 1100px;
        margin: 0 auto;
        padding-bottom: 3rem;
    }
    .walkin-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    .walkin-title {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0 0 0.4rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .walkin-badge {
        background: #dc2626;
        color: #fff;
        font-size: 0.75rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .walkin-subtitle {
        color: #94a3b8;
        font-size: 0.95rem;
        margin: 0;
    }

    /* Stepper Navigation */
    .stepper {
        display: flex;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #64748b;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .step-item.active {
        color: #dc2626;
        background: #fef2f2;
        font-weight: 700;
    }
    .step-item.completed {
        color: #16a34a;
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
    }
    .step-item.active .step-number {
        background: #dc2626;
        color: #ffffff;
    }
    .step-item.completed .step-number {
        background: #16a34a;
        color: #ffffff;
    }

    /* Form Body Card */
    .card-box {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .form-step-pane {
        display: none;
    }
    .form-step-pane.active {
        display: block;
    }
    .section-heading {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Input Grid */
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 768px) {
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.5rem;
    }
    .form-label span.req {
        color: #dc2626;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.92rem;
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-control:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    /* WiFi Plan Selector Cards */
    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
    }
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        background: #ffffff;
    }
    .plan-card:hover {
        border-color: #fca5a5;
        transform: translateY(-2px);
    }
    .plan-card.selected {
        border-color: #dc2626;
        background: #fef2f2;
        box-shadow: 0 8px 20px -4px rgba(220, 38, 38, 0.15);
    }
    .plan-radio {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        accent-color: #dc2626;
        width: 18px;
        height: 18px;
    }
    .plan-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .plan-speed {
        display: inline-block;
        background: #ef4444;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .plan-fee-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.4rem;
    }
    .plan-total-highlight {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #cbd5e1;
        display: flex;
        justify-content: space-between;
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
    }

    /* Payment Method Tabs */
    .pm-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 768px) {
        .pm-options { grid-template-columns: repeat(2, 1fr); }
    }
    .pm-btn {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        font-weight: 700;
        color: #475569;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pm-btn:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }
    .pm-btn.active {
        border-color: #dc2626;
        background: #ffffff;
        color: #dc2626;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
    }
    .pm-box {
        display: none;
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .pm-box.active {
        display: block;
    }

    /* Summary & Highlight box */
    .total-due-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
    }
    .total-due-amount {
        font-size: 2rem;
        font-weight: 900;
        color: #f87171;
    }

    /* Summary Card Table */
    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }
    .summary-table th, .summary-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9rem;
    }
    .summary-table th {
        width: 35%;
        color: #64748b;
        font-weight: 700;
        text-align: left;
        background: #f8fafc;
    }
    .summary-table td {
        font-weight: 600;
        color: #0f172a;
    }

    /* Buttons Footer */
    .step-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    .btn-step {
        padding: 0.75rem 1.75rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.92rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    .btn-prev {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .btn-prev:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .btn-next, .btn-submit {
        background: #dc2626;
        color: #ffffff;
    }
    .btn-next:hover, .btn-submit:hover {
        background: #b91c1c;
    }

    /* Confirmation Modal / Screen */
    .success-modal {
        text-align: center;
        padding: 2.5rem 1.5rem;
    }
    .success-icon {
        width: 80px;
        height: 80px;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        font-size: 2.5rem;
    }
</style>
@endpush

@section('content')
<div class="walkin-container">

    <!-- Header -->
    <div class="walkin-header">
        <div>
            <h1 class="walkin-title">
                CBTVI WiFi Installation
                <span class="walkin-badge">Walk-In Client Portal</span>
            </h1>
            <p class="walkin-subtitle">Register walk-in customers, select broadband plan, schedule installation, and process upfront payments.</p>
        </div>
        <div>
            <a href="{{ route('admin.appointments') }}" class="btn-step btn-prev">
                &larr; Back to Bookings Dashboard
            </a>
        </div>
    </div>

    @if (session('success_message'))
        <div style="background:#dcfce7; color:#15803d; padding: 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #bbf7d0; display:flex; justify-content:space-between; align-items:center;">
            <div>✓ {{ session('success_message') }}</div>
            @if(request()->get('confirmed_id'))
                <a href="{{ route('admin.walkin.receipt', request()->get('confirmed_id')) }}" target="_blank" class="btn-step btn-submit" style="padding:0.4rem 1rem; font-size:0.85rem;">
                    🖨️ Print Receipt
                </a>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div style="background:#fef2f2; color:#b91c1c; padding: 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 500; border: 1px solid #fecaca;">
            <strong style="display:block; margin-bottom:0.4rem;">Please correct the errors below:</strong>
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stepper Progress Bar -->
    <div class="stepper">
        <div class="step-item active" id="step-nav-1">
            <span class="step-number">1</span>
            <span>Client Info</span>
        </div>
        <div class="step-item" id="step-nav-2">
            <span class="step-number">2</span>
            <span>WiFi Plan</span>
        </div>
        <div class="step-item" id="step-nav-3">
            <span class="step-number">3</span>
            <span>Installation Schedule</span>
        </div>
        <div class="step-item" id="step-nav-4">
            <span class="step-number">4</span>
            <span>Payment Method</span>
        </div>
        <div class="step-item" id="step-nav-5">
            <span class="step-number">5</span>
            <span>Booking Summary</span>
        </div>
        <div class="step-item" id="step-nav-6">
            <span class="step-number">6</span>
            <span>Confirmation</span>
        </div>
    </div>

    <!-- Main Form Box -->
    <div class="card-box">
        <form action="{{ route('admin.walkin.store') }}" method="POST" enctype="multipart/form-data" id="walkinForm">
            @csrf

            <!-- STEP 1: Client Information -->
            <div class="form-step-pane active" id="step-pane-1">
                <div class="section-heading">
                    <span>Step 1 — Client Information</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Customer Identity & Address</span>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Juan De La Cruz" value="{{ old('full_name') }}" required
                               data-restrict="name" autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Number <span class="req">*</span></label>
                        <input type="tel" name="contact_no" id="contact_no" class="form-control" placeholder="09171234567" value="{{ old('contact_no') }}" required
                               data-restrict="mobile" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" autocomplete="tel"
                               title="Enter an 11-digit Philippine mobile number starting with 09 (e.g. 09171234567).">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="req">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="juan@example.com" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Municipality <span class="req">*</span></label>
                        <select name="address_municipality" id="address_municipality" class="form-control" required>
                            <option value="">Select Municipality</option>
                            @foreach (\App\Support\ServiceArea::municipalities() as $municipality)
                                <option value="{{ $municipality }}" {{ old('address_municipality', 'Bantayan') == $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Barangay <span class="req">*</span></label>
                        {{-- Filled from the municipality above; see partials/address-age-scripts. --}}
                        <select name="address_barangay" id="address_barangay" class="form-control" required
                                data-barangay-for="address_municipality" data-placeholder="Select Barangay"
                                data-old="{{ old('address_barangay') }}">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Complete Home Address <span class="req">*</span></label>
                        <input type="text" name="complete_address" id="complete_address" class="form-control" placeholder="Street / House No. / Landmark" value="{{ old('complete_address') }}" required
                               data-restrict="address">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Installation Address <span class="req">*</span></label>
                    <input type="text" name="installation_address" id="installation_address" class="form-control" placeholder="Exact address where CBTVI WiFi Router will be installed" value="{{ old('installation_address') }}" required
                           data-restrict="address">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Valid ID Type <span class="req">*</span></label>
                        <select name="valid_id_type" id="valid_id_type" class="form-control" required>
                            <option value="">Select Government / Valid ID</option>
                            <option value="Driver License">Driver's License</option>
                            <option value="Philippine Passport">Philippine Passport</option>
                            <option value="SSS / UMID">SSS / UMID Card</option>
                            <option value="PhilHealth ID">PhilHealth ID</option>
                            <option value="Voter ID">Voter's ID / Certificate</option>
                            <option value="Postal ID">Postal ID</option>
                            <option value="National ID (Philsys)">National ID (Philsys)</option>
                            <option value="PRC ID">PRC ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Valid ID Number <span class="req">*</span></label>
                        <input type="text" name="valid_id_number" id="valid_id_number" class="form-control" placeholder="e.g. N01-12-345678" value="{{ old('valid_id_number') }}" required
                               data-restrict="idnumber" style="text-transform:uppercase;">
                    </div>
                </div>

                <div class="step-actions">
                    <div></div>
                    <button type="button" class="btn-step btn-next" onclick="goToStep(2)">
                        Next: Select WiFi Plan &rarr;
                    </button>
                </div>
            </div>

            <!-- STEP 2: Select WiFi Plan -->
            <div class="form-step-pane" id="step-pane-2">
                <div class="section-heading">
                    <span>Step 2 — Select WiFi Plan</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Choose single broadband package</span>
                </div>

                <div class="plans-grid">
                    @forelse ($services as $srv)
                        @php
                            $instFee = $srv->installation_fee ?? 1000.00;
                            $monthly = $srv->price;
                            $initialTotal = $instFee + $monthly;
                        @endphp
                        <div class="plan-card {{ $loop->first ? 'selected' : '' }}" onclick="selectPlanCard(this, '{{ $srv->id }}', '{{ addslashes($srv->service_name) }}', '{{ $srv->speed ?? 'High Speed' }}', {{ $monthly }}, {{ $instFee }})">
                            <input type="radio" name="service_id" value="{{ $srv->id }}" class="plan-radio" {{ $loop->first ? 'checked' : '' }} required>
                            <div class="plan-title">{{ $srv->service_name }}</div>
                            <span class="plan-speed">⚡ {{ $srv->speed ?? 'Fiber Fast' }}</span>

                            <div class="plan-fee-item">
                                <span>Monthly Subscription:</span>
                                <strong>₱{{ number_format($monthly, 2) }}</strong>
                            </div>
                            <div class="plan-fee-item">
                                <span>Installation Fee:</span>
                                <strong>₱{{ number_format($instFee, 2) }}</strong>
                            </div>
                            
                            <div class="plan-total-highlight">
                                <span>Total Initial Payment:</span>
                                <span style="color:#dc2626;">₱{{ number_format($initialTotal, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align:center; padding:2rem; color:#64748b;">
                            No active CBTVI Broadband plans found. Please add plans under Admin Services.
                        </div>
                    @endforelse
                </div>

                <div class="step-actions">
                    <button type="button" class="btn-step btn-prev" onclick="goToStep(1)">
                        &larr; Back to Client Info
                    </button>
                    <button type="button" class="btn-step btn-next" onclick="goToStep(3)">
                        Next: Installation Schedule &rarr;
                    </button>
                </div>
            </div>

            <!-- STEP 3: Installation Schedule -->
            <div class="form-step-pane" id="step-pane-3">
                <div class="section-heading">
                    <span>Step 3 — Installation Schedule</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Preferred Date & Time Slot</span>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Preferred Installation Date <span class="req">*</span></label>
                        <input type="date" name="preferred_date" id="preferred_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('preferred_date', date('Y-m-d', strtotime('+1 day'))) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Installation Time <span class="req">*</span></label>
                        <select name="preferred_time" id="preferred_time" class="form-control" required>
                            <option value="">Select Time Slot</option>
                            @forelse ($timeSlots as $slot)
                                @php
                                    $isOt = strtotime($slot->slot_time) >= strtotime('18:00:00');
                                @endphp
                                <option value="{{ $slot->slot_time }}">
                                    {{ date('h:i A', strtotime($slot->slot_time)) }}{{ $isOt ? ' (Overtime)' : '' }}
                                </option>
                            @empty
                                <option value="08:00:00">08:00 AM - Morning Slot</option>
                                <option value="10:00:00">10:00 AM - Mid-Morning Slot</option>
                                <option value="12:00:00">12:00 PM - Midday Slot</option>
                                <option value="14:00:00">02:00 PM - Afternoon Slot</option>
                                <option value="16:00:00">04:00 PM - Late Afternoon Slot</option>
                                <option value="18:00:00">06:00 PM - Overtime Slot</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Installation Notes / Special Instructions</label>
                    <textarea name="installation_notes" id="installation_notes" class="form-control" rows="3" placeholder="Provide additional directions, gate codes, or special technician instructions..."></textarea>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn-step btn-prev" onclick="goToStep(2)">
                        &larr; Back to WiFi Plan
                    </button>
                    <button type="button" class="btn-step btn-next" onclick="goToStep(4)">
                        Next: Payment Method &rarr;
                    </button>
                </div>
            </div>

            <!-- STEP 4: Payment Method -->
            <div class="form-step-pane" id="step-pane-4">
                <div class="section-heading">
                    <span>Step 4 — Payment Method</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Collect or Schedule Initial Payment</span>
                </div>

                <label class="form-label">Select Upfront Payment Method <span class="req">*</span></label>
                <div class="pm-options">
                    <div class="pm-btn active" onclick="selectPaymentMethod('Cash', this)">
                        💵 Cash
                    </div>
                    <div class="pm-btn" onclick="selectPaymentMethod('GCash', this)">
                        📱 GCash
                    </div>
                    <div class="pm-btn" onclick="selectPaymentMethod('Bank Transfer', this)">
                        🏦 Bank Transfer
                    </div>
                    <div class="pm-btn" onclick="selectPaymentMethod('Pay Later', this)">
                        ⏳ Pay Later
                    </div>
                </div>
                <input type="hidden" name="payment_method" id="selected_payment_method" value="Cash">

                <!-- Cash Box -->
                <div class="pm-box active" id="pm-box-Cash">
                    <h4 style="margin:0 0 1rem 0; font-weight:800; color:#0f172a;">Cash Payment Details</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Amount Received (₱)</label>
                            <input type="number" step="0.01" name="cash_received" id="cash_received" class="form-control" placeholder="0.00" oninput="calculateChange()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Calculated Change (₱)</label>
                            <input type="text" id="cash_change_display" class="form-control" value="₱0.00" readonly style="background:#e2e8f0; font-weight:800; color:#16a34a;">
                        </div>
                    </div>
                    <div style="font-size:0.85rem; color:#64748b;">
                        Payment Status will automatically set to <strong>Payment Confirmed</strong> upon submitting full payment.
                    </div>
                </div>

                <!-- GCash Box -->
                <div class="pm-box" id="pm-box-GCash">
                    <h4 style="margin:0 0 1rem 0; font-weight:800; color:#0f172a;">GCash Digital Payment</h4>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">GCash Ref Number</label>
                            <input type="text" name="gcash_ref" class="form-control" placeholder="e.g. 1002938481">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Amount Paid (₱)</label>
                            <input type="number" step="0.01" name="gcash_amount" id="gcash_amount" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Date</label>
                            <input type="datetime-local" name="gcash_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload GCash Payment Proof (Screenshot)</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Bank Transfer Box -->
                <div class="pm-box" id="pm-box-Bank Transfer">
                    <h4 style="margin:0 0 1rem 0; font-weight:800; color:#0f172a;">Bank Transfer Details</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. BDO, BPI, Landbank">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference / Transaction No.</label>
                            <input type="text" name="bank_ref" class="form-control" placeholder="e.g. TRX-99201">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Amount Paid (₱)</label>
                            <input type="number" step="0.01" name="bank_amount" id="bank_amount" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Date</label>
                            <input type="datetime-local" name="bank_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload Bank Transfer Deposit Slip / Proof</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Pay Later Box -->
                <div class="pm-box" id="pm-box-Pay Later">
                    <h4 style="margin:0 0 1rem 0; font-weight:800; color:#0f172a;">Pay Later Agreement</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Total Amount Due (₱)</label>
                            <input type="text" id="pay_later_amount_display" class="form-control" readonly style="background:#e2e8f0; font-weight:800;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Promised Due Date</label>
                            <input type="date" name="pay_later_due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                    </div>
                    <div style="background:#fff7ed; border:1px solid #ffedd5; color:#c2410c; padding:0.85rem; border-radius:8px; font-size:0.85rem; font-weight:600;">
                        ℹ️ Booking will be registered as <strong>Pending Payment</strong>. Installation schedule remains reserved.
                    </div>
                </div>

                <!-- Total Amount Due Highlight -->
                <div class="total-due-banner">
                    <div>
                        <div style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em; color:#94a3b8; font-weight:700;">Formula: Monthly Subscription + Installation Fee</div>
                        <div style="font-size:1.15rem; font-weight:800;">TOTAL AMOUNT DUE</div>
                    </div>
                    <div class="total-due-amount" id="total_amount_due_display">₱0.00</div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn-step btn-prev" onclick="goToStep(3)">
                        &larr; Back to Schedule
                    </button>
                    <button type="button" class="btn-step btn-next" onclick="goToStep(5)">
                        Next: Review Booking Summary &rarr;
                    </button>
                </div>
            </div>

            <!-- STEP 5: Booking Summary -->
            <div class="form-step-pane" id="step-pane-5">
                <div class="section-heading">
                    <span>Step 5 — Booking Summary</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Verify Details Before Confirmation</span>
                </div>

                <h3 style="margin-bottom:1rem; font-weight:800; color:#dc2626;">CBTVI WiFi Installation Booking</h3>

                <table class="summary-table">
                    <tr>
                        <th>Customer Name</th>
                        <td id="sum_customer_name">-</td>
                    </tr>
                    <tr>
                        <th>Contact Number</th>
                        <td id="sum_contact_no">-</td>
                    </tr>
                    <tr>
                        <th>Installation Address</th>
                        <td id="sum_installation_address">-</td>
                    </tr>
                    <tr>
                        <th>Barangay / Municipality</th>
                        <td id="sum_address_area">-</td>
                    </tr>
                    <tr>
                        <th>Selected Plan</th>
                        <td id="sum_plan_name">-</td>
                    </tr>
                    <tr>
                        <th>Internet Speed</th>
                        <td id="sum_speed">-</td>
                    </tr>
                    <tr>
                        <th>Installation Date & Time</th>
                        <td id="sum_schedule">-</td>
                    </tr>
                    <tr>
                        <th>Monthly Fee</th>
                        <td id="sum_monthly_fee">₱0.00</td>
                    </tr>
                    <tr>
                        <th>Installation Fee</th>
                        <td id="sum_installation_fee">₱0.00</td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td id="sum_payment_method">Cash</td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td id="sum_amount_paid">₱0.00</td>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <td id="sum_payment_status"><span class="walkin-badge" style="background:#16a34a;">Payment Confirmed</span></td>
                    </tr>
                </table>

                <div class="step-actions">
                    <div>
                        <button type="button" class="btn-step btn-prev" onclick="goToStep(1)" style="margin-right:0.5rem;">
                            ✎ Edit Information
                        </button>
                        <button type="button" class="btn-step btn-prev" onclick="goToStep(4)">
                            &larr; Back to Payment
                        </button>
                    </div>
                    <button type="submit" class="btn-step btn-submit" id="btnConfirmBooking">
                        ✓ Confirm Booking & Generate Receipt
                    </button>
                </div>
            </div>

            <!-- STEP 6: Confirmation -->
            <div class="form-step-pane" id="step-pane-6">
                <div class="section-heading">
                    <span>Step 6 — Confirmation</span>
                    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Booking Recorded</span>
                </div>

                @if (!empty($confirmedBooking))
                    <div style="text-align:center; padding:1.5rem 0 2rem;">
                        <div style="width:76px; height:76px; border-radius:50%; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h3 style="font-size:1.4rem; font-weight:800; color:#0f172a; margin-bottom:0.4rem;">Booking Confirmed</h3>
                        <p style="color:#64748b; margin:0;">
                            The walk-in booking has been recorded and the client account is now active.
                        </p>
                    </div>

                    <table class="summary-table">
                        <tr>
                            <th>Booking Reference</th>
                            <td><strong style="color:#dc2626;">{{ $confirmedBooking->booking_ref ?? ('#' . str_pad($confirmedBooking->id, 5, '0', STR_PAD_LEFT)) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <td>
                                {{ $confirmedBooking->client->firstname ?? '' }} {{ $confirmedBooking->client->lastname ?? '' }}
                                @if (!empty($confirmedBooking->client->account_number))
                                    &middot; Acct {{ $confirmedBooking->client->account_number }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Selected Plan</th>
                            <td>{{ $confirmedBooking->service->service_name ?? 'WiFi Plan' }}</td>
                        </tr>
                        <tr>
                            <th>Installation Address</th>
                            <td>{{ $confirmedBooking->installation_address }}</td>
                        </tr>
                        <tr>
                            <th>Installation Date & Time</th>
                            <td>
                                {{ date('M d, Y', strtotime($confirmedBooking->preferred_date)) }}
                                at {{ date('h:i A', strtotime($confirmedBooking->preferred_time)) }}
                            </td>
                        </tr>
                        <tr>
                            <th>Amount Paid</th>
                            <td>₱{{ number_format((float) $confirmedBooking->amount_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Payment Status</th>
                            <td>
                                <span class="walkin-badge" style="background:{{ $confirmedBooking->payment_status === 'Payment Confirmed' ? '#16a34a' : '#ea580c' }};">
                                    {{ $confirmedBooking->payment_status }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    <div class="step-actions">
                        <a href="{{ route('admin.walkin.create') }}" class="btn-step btn-prev">
                            + New Walk-In Booking
                        </a>
                        <div>
                            <a href="{{ route('admin.appointments') }}" class="btn-step btn-prev" style="margin-right:0.5rem;">
                                View All Bookings
                            </a>
                            <a href="{{ route('admin.walkin.receipt', $confirmedBooking->id) }}" target="_blank" class="btn-step btn-submit">
                                🖨️ Print Receipt
                            </a>
                        </div>
                    </div>
                @else
                    <p style="color:#64748b;">Complete the booking summary to generate a confirmation.</p>
                    <div class="step-actions">
                        <button type="button" class="btn-step btn-prev" onclick="goToStep(5)">
                            &larr; Back to Booking Summary
                        </button>
                        <div></div>
                    </div>
                @endif
            </div>

        </form>
    </div>

</div>

@push('scripts')
<script src="{{ asset('assets/js/form-restrictions.js') }}?v={{ filemtime(public_path('assets/js/form-restrictions.js')) }}"></script>
@include('partials.address-age-scripts')
<script>
    let currentStep = 1;
    let selectedPlan = {
        id: '',
        name: '',
        speed: '',
        monthly: 0,
        instFee: 0
    };

    // The municipality -> barangay cascade lives in assets/js/address-age.js and
    // is wired by the data attributes on the two selects.

    document.addEventListener('DOMContentLoaded', function() {
        // Auto select first plan card if available
        const firstCard = document.querySelector('.plan-card');
        if (firstCard) {
            firstCard.click();
        }

        // Required fields on hidden panes cannot be focused, which makes the browser
        // block submit silently — so the wizard validates the panes itself instead.
        const form = document.getElementById('walkinForm');
        form.setAttribute('novalidate', 'novalidate');
        form.addEventListener('submit', function (e) {
            for (let step = 1; step <= 5; step++) {
                if (!validateStep(step)) {
                    e.preventDefault();
                    return;
                }
            }
        });

        @if (!empty($confirmedBooking))
            goToStep(6, true);
        @endif
    });

    function firstInvalidIn(step) {
        const pane = document.getElementById(`step-pane-${step}`);
        if (!pane) return null;
        const controls = Array.from(pane.querySelectorAll('input, select, textarea'));
        for (const control of controls) {
            if (!control.checkValidity()) return control;
        }
        return null;
    }

    function validateStep(step) {
        const pane = document.getElementById(`step-pane-${step}`);

        // Restricted fields report through their own red inline message, so
        // check them first and let that message stand instead of a browser
        // tooltip firing on top of it.
        if (pane && window.FieldRestrict && !window.FieldRestrict.validateWithin(pane)) {
            if (currentStep !== step) goToStep(step, true);
            const flagged = pane.querySelector('[data-restrict].fr-invalid');
            if (flagged) flagged.focus();
            return false;
        }

        const invalid = firstInvalidIn(step);
        if (!invalid) return true;
        if (currentStep !== step) goToStep(step, true);
        invalid.reportValidity();
        return false;
    }

    function goToStep(step, skipValidation) {
        // Moving forward only allowed once every step in between is complete
        if (!skipValidation && step > currentStep) {
            for (let i = currentStep; i < step; i++) {
                if (!validateStep(i)) return;
            }
        }

        // Hide current pane
        document.querySelectorAll('.form-step-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active', 'completed'));

        currentStep = step;

        document.getElementById(`step-pane-${step}`).classList.add('active');
        document.getElementById(`step-nav-${step}`).classList.add('active');

        // Highlight completed
        for (let i = 1; i < step; i++) {
            document.getElementById(`step-nav-${i}`).classList.add('completed');
        }

        if (step === 5) {
            buildSummary();
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function selectPlanCard(cardEl, id, name, speed, monthly, instFee) {
        document.querySelectorAll('.plan-card').forEach(el => el.classList.remove('selected'));
        cardEl.classList.add('selected');
        
        const radio = cardEl.querySelector('.plan-radio');
        if (radio) radio.checked = true;

        selectedPlan = { id, name, speed, monthly: parseFloat(monthly), instFee: parseFloat(instFee) };

        updateCalculations();
    }

    function updateCalculations() {
        const total = selectedPlan.monthly + selectedPlan.instFee;
        const formattedTotal = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        document.getElementById('total_amount_due_display').innerText = formattedTotal;
        document.getElementById('pay_later_amount_display').value = formattedTotal;

        // Auto prefill cash received
        const cashInput = document.getElementById('cash_received');
        if (!cashInput.value) {
            cashInput.value = total.toFixed(2);
        }
        calculateChange();
    }

    function calculateChange() {
        const total = selectedPlan.monthly + selectedPlan.instFee;
        const received = parseFloat(document.getElementById('cash_received').value) || 0;
        const change = Math.max(0, received - total);

        document.getElementById('cash_change_display').value = '₱' + change.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectPaymentMethod(pm, btnEl) {
        document.querySelectorAll('.pm-btn').forEach(b => b.classList.remove('active'));
        btnEl.classList.add('active');

        document.getElementById('selected_payment_method').value = pm;

        document.querySelectorAll('.pm-box').forEach(box => box.classList.remove('active'));
        const targetBox = document.getElementById(`pm-box-${pm}`);
        if (targetBox) targetBox.classList.add('active');
    }

    function buildSummary() {
        const name = document.getElementById('full_name').value;
        const contact = document.getElementById('contact_no').value;
        const addr = document.getElementById('installation_address').value;
        const pDate = document.getElementById('preferred_date').value;
        const pTime = document.getElementById('preferred_time').value;
        const pm = document.getElementById('selected_payment_method').value;

        document.getElementById('sum_customer_name').innerText = name;
        document.getElementById('sum_contact_no').innerText = contact;
        document.getElementById('sum_installation_address').innerText = addr;
        document.getElementById('sum_address_area').innerText =
            document.getElementById('address_barangay').value + ', ' +
            document.getElementById('address_municipality').value + ', Cebu';
        document.getElementById('sum_plan_name').innerText = selectedPlan.name;
        document.getElementById('sum_speed').innerText = selectedPlan.speed;
        document.getElementById('sum_schedule').innerText = `${pDate} at ${pTime}`;
        document.getElementById('sum_monthly_fee').innerText = '₱' + selectedPlan.monthly.toFixed(2);
        document.getElementById('sum_installation_fee').innerText = '₱' + selectedPlan.instFee.toFixed(2);
        document.getElementById('sum_payment_method').innerText = pm;

        const total = selectedPlan.monthly + selectedPlan.instFee;
        let amountPaid = 0;
        let pStatus = 'Payment Confirmed';

        if (pm === 'Cash') {
            amountPaid = parseFloat(document.getElementById('cash_received').value) || total;
        } else if (pm === 'GCash') {
            amountPaid = parseFloat(document.getElementById('gcash_amount').value) || total;
        } else if (pm === 'Bank Transfer') {
            amountPaid = parseFloat(document.getElementById('bank_amount').value) || total;
        } else if (pm === 'Pay Later') {
            amountPaid = 0;
            pStatus = 'Pending Payment';
        }

        document.getElementById('sum_amount_paid').innerText = '₱' + amountPaid.toFixed(2);
        document.getElementById('sum_payment_status').innerHTML = pStatus === 'Payment Confirmed' 
            ? `<span class="walkin-badge" style="background:#16a34a;">Payment Confirmed</span>`
            : `<span class="walkin-badge" style="background:#ea580c;">Pending Payment</span>`;
    }

</script>
@endpush
@endsection
