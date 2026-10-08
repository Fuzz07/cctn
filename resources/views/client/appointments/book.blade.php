@extends('layouts.app')

@section('title', 'Book a Service - BCTVI Bantayan')

@push('styles')
<style>
    .book-card-header {
        background: linear-gradient(135deg, #a50000, #7b0000);
        margin: -2rem -2rem 2rem;
        padding: 2rem;
        border-radius: 14px 14px 0 0;
        text-align: center;
    }
    .book-card-header h2 { color: #fff; margin: 0; font-size: 1.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .book-card-header p  { color: rgba(255,255,255,0.65); font-size: 0.82rem; margin-top: 0.3rem; }
    
    .antigravity-card {
        background: var(--bg-card);
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        padding: 2rem;
        border: 1px solid var(--border-light);
    }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b; font-size: 0.9rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }

    /* ── Installation Type picker ── */
    .install-type-group { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .install-type-card {
        display: flex; flex-direction: column; gap: 0.15rem;
        padding: 0.9rem 1rem; border: 1px solid var(--border); border-radius: 10px;
        background: #fff; cursor: pointer; transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .install-type-card:hover { border-color: #dc2626; }
    .install-type-card input { position: absolute; opacity: 0; width: 0; height: 0; }
    .install-type-card:focus-within { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    .install-type-card.is-selected { border-color: #dc2626; background: #fef2f2; box-shadow: 0 0 0 1px #dc2626 inset; }
    .install-type-name { font-weight: 700; font-size: 0.95rem; color: #1e293b; }
    .install-type-hint { font-size: 0.78rem; color: #64748b; }
    .install-type-group.has-error .install-type-card { border-color: #dc2626; }
    @media (max-width: 520px) { .install-type-group { grid-template-columns: 1fr; } }

    /* ── Validation States ── */
    .form-control.is-invalid {
        border-color: #dc2626 !important;
        background-color: #fff8f8;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }
    .field-error {
        display: none;
        margin-top: 0.35rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .field-error.visible { display: flex; }
    .slots-error-msg {
        display: none;
        margin-top: 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #dc2626;
        align-items: center;
        gap: 0.3rem;
    }
    .slots-error-msg.visible { display: flex; }
    .slots-container.has-error {
        border: 1.5px solid #dc2626;
        border-radius: 8px;
        padding: 0.5rem;
        background: #fff8f8;
    }

    /* ── Validation Banner ── */
    #validation-banner {
        display: none;
        background: linear-gradient(135deg, #fef2f2, #fff5f5);
        border: 1.5px solid #fca5a5;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        animation: shake 0.4s ease;
    }
    #validation-banner.visible { display: block; }
    #validation-banner .banner-title {
        font-weight: 800;
        font-size: 0.92rem;
        color: #991b1b;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }
    #validation-banner ul {
        margin: 0;
        padding-left: 1.2rem;
        color: #b91c1c;
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.7;
    }
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        20%      { transform: translateX(-5px); }
        40%      { transform: translateX(5px); }
        60%      { transform: translateX(-4px); }
        80%      { transform: translateX(4px); }
    }
    
    .slots-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
    }
    .slot-radio { display: none; }
    .slot-label {
        display: block;
        padding: 0.75rem;
        text-align: center;
        background: #f9fafb;
        border: 1.5px solid var(--border-light);
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        color: #475569;
        transition: all 0.2s;
    }
    .slot-label:hover:not(.unavailable) { border-color: #dc2626; background: #fef2f2; color: #dc2626; }
    .slot-radio:checked + .slot-label { background: #dc2626; border-color: #dc2626; color: #fff; box-shadow: 0 4px 10px rgba(220,38,38,0.3); }
    .slot-label.unavailable { opacity: 0.4; cursor: not-allowed; background: #f3f4f6; text-decoration: line-through; }
    .slot-label.slot-overtime { border-color: #f59e0b; }
    .slot-ot-tag {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        color: #d97706;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }
    .slot-radio:checked + .slot-label .slot-ot-tag { color: #fef08a; }

    .btn-submit {
        background: #0f172a;
        color: #fff;
        border: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        margin-top: 1rem;
    }
    .btn-submit:hover { background: #dc2626; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(220,38,38,0.25); }
    .text-gold { color: #d97706; }
</style>
@endpush

@section('content')
<div style="max-width: 700px; margin: 2rem auto; width: 100%; padding: 0 1rem;" class="fade-in">
    <div class="antigravity-card" style="animation: none;">
        <div class="book-card-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="2" style="margin-bottom: 0.5rem;">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <h2>Book an Appointment</h2>
            <p>Schedule a WiFi installation, subscription inquiry, or technical support visit</p>
        </div>

        <form action="{{ route('client.book.submit') }}" method="POST" id="booking-form" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- ── Server-side Validation Error Banner ── --}}
            @if ($errors->any())
                <div id="validation-banner" class="visible">
                    <div class="banner-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Please fix the following errors before submitting:
                    </div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div id="validation-banner"></div>
            @endif

            <!-- Installation Type (gates the service list below) -->
            @php($chosenType = old('installation_type', $selectedInstallationType))
            <div class="form-group">
                <label class="form-label" id="installation-type-label">Select Installation Type <span style="color:#dc2626">*</span></label>
                <div class="install-type-group {{ $errors->has('installation_type') ? 'has-error' : '' }}"
                     id="installation-type-group" role="radiogroup" aria-labelledby="installation-type-label">
                    @foreach ([
                        'residential' => ['Residential', 'For homes and personal use'],
                        'business'    => ['Business', 'For offices, shops and enterprises'],
                    ] as $typeValue => $typeMeta)
                        <label class="install-type-card {{ $chosenType === $typeValue ? 'is-selected' : '' }}">
                            <input type="radio" name="installation_type" value="{{ $typeValue }}"
                                   {{ $chosenType === $typeValue ? 'checked' : '' }}>
                            <span class="install-type-name">{{ $typeMeta[0] }}</span>
                            <span class="install-type-hint">{{ $typeMeta[1] }}</span>
                        </label>
                    @endforeach
                </div>
                <span class="field-error {{ $errors->has('installation_type') ? 'visible' : '' }}" id="error-installation_type">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('installation_type', 'Please select an installation type.') }}
                </span>
            </div>

            <!-- Service Selection -->
            <div class="form-group">
                <label class="form-label" for="service_id">Select Service <span style="color:#dc2626">*</span></label>
                <select name="service_id" id="service_id" class="form-control {{ $errors->has('service_id') ? 'is-invalid' : '' }}"
                        data-placeholder-empty="Choose a service package..."
                        data-placeholder-locked="Select an installation type first...">
                    <option value="">Choose a service package...</option>
                    @foreach ($services as $serv)
                        <option value="{{ $serv->id }}" data-account-type="{{ $serv->account_type }}"
                            {{ (old('service_id', $preselectedServiceId) == $serv->id) ? 'selected' : '' }}>
                            {{ $serv->service_name }} - ₱{{ number_format($serv->price, 2) }}
                        </option>
                    @endforeach
                </select>
                <span class="field-error {{ $errors->has('service_id') ? 'visible' : '' }}" id="error-service_id">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('service_id', 'Please select a service package.') }}
                </span>
            </div>

            <!-- Installation site locator (profile only stores barangay + municipality) -->
            <div class="form-group">
                <label class="form-label" for="purok_landmark">Purok / Street &amp; Nearby Landmark <span style="color:#dc2626">*</span></label>
                <input type="text" name="purok_landmark" id="purok_landmark" maxlength="255"
                       class="form-control {{ $errors->has('purok_landmark') ? 'is-invalid' : '' }}"
                       value="{{ old('purok_landmark') }}"
                       placeholder="e.g. Purok 3 Mabini St., beside the barangay chapel">
                <small style="display:block; margin-top:.35rem; color:#64748b; font-size:.78rem;">
                    @if ($client->address_barangay || $client->address_municipality)
                        Your barangay ({{ trim(($client->address_barangay ?: '') . ', ' . ($client->address_municipality ?: ''), ', ') }}) is already on file &mdash;
                    @endif
                    add the purok or street and a nearby landmark so our technician can find your place.
                </small>
                <span class="field-error {{ $errors->has('purok_landmark') ? 'visible' : '' }}" id="error-purok_landmark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('purok_landmark', 'Please enter your purok / street and a nearby landmark.') }}
                </span>
            </div>

            <!-- Date Selection (reloads page to fetch timeslots) -->
            <div class="form-group">
                <label class="form-label" for="preferred_date">Preferred Date <span style="color:#dc2626">*</span></label>
                <input type="date" name="preferred_date" id="preferred_date"
                       class="form-control {{ $errors->has('preferred_date') ? 'is-invalid' : '' }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       value="{{ old('preferred_date', $selectedDate) }}">
                <span class="field-error {{ $errors->has('preferred_date') ? 'visible' : '' }}" id="error-preferred_date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('preferred_date', 'Please select a preferred date.') }}
                </span>
                <p class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem;">Changing the date automatically updates the list of available slots below.</p>
            </div>

            <!-- Time Slot Radio Grid -->
            <div class="form-group">
                <label class="form-label">Available Time Slots for <span class="text-gold" id="display-date">{{ date('F d, Y', strtotime($selectedDate)) }}</span> <span style="color:#dc2626">*</span></label>

                <div class="slots-container {{ $errors->has('preferred_time') ? 'has-error' : '' }}" id="slots-container">
                    @forelse ($allSlots as $slot_time)
                        @php
                            $is_booked = in_array($slot_time, $bookedSlots);
                            $is_overtime = strtotime($slot_time) >= strtotime('18:00:00');
                            $formatted_time = date('h:i A', strtotime($slot_time));
                        @endphp
                        <div>
                            <input type="radio" name="preferred_time" id="slot_{{ $slot_time }}" 
                                   value="{{ $slot_time }}" class="slot-radio" 
                                   {{ $is_booked ? 'disabled' : '' }} required>
                            
                            <label for="slot_{{ $slot_time }}" 
                                   class="slot-label {{ $is_booked ? 'unavailable' : '' }} {{ $is_overtime ? 'slot-overtime' : '' }}">
                                {{ $formatted_time }}
                                @if($is_overtime)
                                    <span class="slot-ot-tag">Overtime</span>
                                @endif
                            </label>
                        </div>
                    @empty
                        <p class="text-muted" style="grid-column: 1 / -1;">No time slots configured in the database.</p>
                    @endforelse
                </div>

                <span class="slots-error-msg {{ $errors->has('preferred_time') ? 'visible' : '' }}" id="error-preferred_time">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('preferred_time', 'Please select an available time slot.') }}
                </span>

                <!-- Slot Legend -->
                <div style="display: flex; gap: 1rem; margin-top: 0.75rem; font-size: 0.8rem; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f9fafb; border: 1.5px solid var(--border-light); border-radius: 3px;"></span> Available Slot
                    </span>
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f9fafb; border: 1.5px solid #f59e0b; border-radius: 3px;"></span> Overtime Slot
                    </span>
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #dc2626; border-radius: 3px;"></span> Selected Slot
                    </span>
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f3f4f6; opacity: 0.4; border: 1.5px solid #e5e7eb; border-radius: 3px;"></span> Booked (Unavailable)
                    </span>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="form-group" style="background: #fafafa; border: 1px solid var(--border-light); border-radius: 12px; padding: 1.25rem;">
                <label class="form-label" for="payment_method" style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-dark); margin-bottom: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Select Digital Payment Method <span style="color:#dc2626">*</span>
                </label>

                <select name="payment_method" id="payment_method" class="form-control" required style="font-weight: 600;" onchange="updatePaymentInstructions()">
                    <option value="GCash" {{ old('payment_method', 'GCash') == 'GCash' ? 'selected' : '' }}>GCash (E-Wallet)</option>
                </select>

                <!-- DYNAMIC PAYMENT INSTRUCTIONS CARD -->
                <div id="payment_instructions_box" style="margin-top: 1.25rem; background: var(--bg-page); border: 1.5px solid var(--border-light); border-radius: 14px; padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: #dc2626; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                            <i class="bi bi-info-circle-fill"></i> Payment Account Instructions
                        </span>
                        <span id="instruction_provider_badge" style="background: #dbeafe; color: #1d4ed8; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.65rem; border-radius: 99px;">
                            GCash
                        </span>
                    </div>

                    <!-- Dynamic Account Info Display -->
                    <div id="account_details_content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <!-- Injected via JavaScript -->
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" for="reference_number" style="font-size: 0.85rem; font-weight: 700; color: var(--text-body);">
                            Reference / Transaction Number <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="reference_number" id="reference_number"
                               class="form-control {{ $errors->has('reference_number') ? 'is-invalid' : '' }}"
                               placeholder="e.g. 10029384756"
                               value="{{ old('reference_number') }}"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               oninput="this.value = this.value.replace(/\D/g, '')"
                               style="font-family: monospace; font-size: 0.95rem; font-weight: 700;">
                        <span class="field-error {{ $errors->has('reference_number') ? 'visible' : '' }}" id="error-reference_number">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $errors->first('reference_number', 'Please enter your payment reference/transaction number.') }}
                        </span>
                        <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 0.25rem;">Enter the reference or reference ID from your payment confirmation screen (numbers only).</small>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <label class="form-label" for="payment_proof" style="font-size: 0.85rem; font-weight: 700; color: var(--text-body); margin-bottom: 0;">
                                Upload Payment Receipt or Screenshot <span style="color: #dc2626;">*</span>
                            </label>
                            <button type="button" onclick="toggleSampleReceiptModal(true)" style="background: none; border: none; color: #1d4ed8; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                View Sample Receipt
                            </button>
                        </div>
                        <input type="file" name="payment_proof" id="payment_proof"
                               class="form-control {{ $errors->has('payment_proof') ? 'is-invalid' : '' }}"
                               accept="image/jpeg,image/png,image/jpg,image/webp">
                        <span class="field-error {{ $errors->has('payment_proof') ? 'visible' : '' }}" id="error-payment_proof">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span id="error-payment_proof-text">{{ $errors->first('payment_proof', 'Please upload a vertical screenshot of your GCash receipt.') }}</span>
                        </span>
                        
                        <!-- Policy Callout -->
                        <div style="margin-top: 0.5rem; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 4px; padding: 0.5rem 0.75rem; font-size: 0.78rem; color: #1e40af;">
                            <strong>GCash Receipt Required:</strong> Only official GCash payment receipts submitted through this interface with a visible reference number will be accepted.
                        </div>

                        <!-- Preview Card -->
                        <div id="payment_proof_preview" style="display: none; margin-top: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); align-items: center; gap: 0.75rem;">
                            <img id="payment_proof_img" src="" alt="Receipt Preview" style="width: 48px; height: 72px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); flex-shrink: 0;">
                            <div style="flex: 1; min-width: 0;">
                                <div id="payment_proof_name" style="font-weight: 700; font-size: 0.82rem; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                                <div id="payment_proof_meta" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;"></div>
                                <div style="font-size: 0.72rem; color: #16a34a; font-weight: 700; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    Valid Portrait Receipt Screenshot
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Status Notice -->
                    <div style="margin-top: 1rem; background: #fffbebf8; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8rem; color: #92400e; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1rem;">⏳</span>
                        <span>
                            <strong>Note:</strong> After booking, your payment status will remain <strong>Pending Verification</strong> until our administrator verifies your submitted transaction reference and screenshot.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Additional Message -->
            <div class="form-group">
                <label class="form-label" for="message">Additional Message (Optional)</label>
                <textarea name="message" id="message" class="form-control" rows="3" placeholder="Any specific requirements or notes regarding location/landmarks?">{{ old('message') }}</textarea>
            </div>

            <button type="button" id="submit-btn" class="btn-submit" onclick="handleFormSubmit()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Confirm Appointment Booking
            </button>
        </form>
    </div>
</div>

<!-- Sample GCash Receipt Modal -->
<div id="sampleReceiptModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) toggleSampleReceiptModal(false)">
    <div style="background: #ffffff; border-radius: 16px; max-width: 400px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #2563eb;"></span>
                Sample GCash Receipt
            </h3>
            <button type="button" onclick="toggleSampleReceiptModal(false)" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <div style="padding: 1.25rem 1.5rem; text-align: center;">
            <div style="margin-bottom: 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.75rem; font-size: 0.8rem; color: #1e40af; text-align: left;">
                <strong>GCash Receipt Required:</strong> Please make sure your uploaded screenshot clearly shows the <strong>Total Amount Sent</strong> and <strong>Ref No. / Reference Number</strong> in vertical (portrait) view.
            </div>
            <img src="{{ asset('assets/images/gcash-sample-receipt.png') }}"
                 alt="Valid GCash receipt sample"
                 style="width: 100%; max-width: 280px; height: auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; display: inline-block;">
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; text-align: right;">
            <button type="button" onclick="toggleSampleReceiptModal(false)" style="background: #2563eb; color: #fff; font-weight: 600; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer;">
                Got it
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleSampleReceiptModal(show) {
        const modal = document.getElementById('sampleReceiptModal');
        if (modal) {
            modal.style.display = show ? 'flex' : 'none';
        }
    }

    /* ══════════════════════════════════════════════
       CLIENT-SIDE FORM VALIDATION
    ══════════════════════════════════════════════ */
    let isPaymentProofValid = false;

    function setFieldError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorEl = document.getElementById(errorId);
        if (field) field.classList.add('is-invalid');
        if (errorEl) {
            const errorTextSpan = document.getElementById(errorId + '-text');
            if (errorTextSpan) {
                errorTextSpan.textContent = message;
            } else {
                const svg = errorEl.querySelector('svg');
                if (svg && svg.nextSibling) {
                    svg.nextSibling.textContent = ' ' + message;
                } else {
                    errorEl.textContent = message;
                }
            }
            errorEl.classList.add('visible');
        }
    }

    function clearFieldError(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorEl = document.getElementById(errorId);
        if (field) field.classList.remove('is-invalid');
        if (errorEl) errorEl.classList.remove('visible');
    }

    function clearAllErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.field-error.visible, .slots-error-msg.visible').forEach(el => el.classList.remove('visible'));
        const slotsContainer = document.getElementById('slots-container');
        if (slotsContainer) slotsContainer.classList.remove('has-error');
        const typeGroup = document.getElementById('installation-type-group');
        if (typeGroup) typeGroup.classList.remove('has-error');
        hideBanner();
    }

    function showBanner(errors) {
        const banner = document.getElementById('validation-banner');
        if (!banner) return;
        const existingList = banner.querySelector('ul');
        if (existingList) existingList.remove();
        banner.innerHTML = `
            <div class="banner-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Please complete all required fields before submitting:
            </div>
            <ul>${errors.map(e => `<li>${e}</li>`).join('')}</ul>
        `;
        banner.classList.add('visible');
        banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideBanner() {
        const banner = document.getElementById('validation-banner');
        if (banner) banner.classList.remove('visible');
    }

    function validateBookingForm() {
        clearAllErrors();
        const errors = [];

        // 1. Installation Type — gates the service list, so it is checked first
        if (!getInstallationType()) {
            const typeGroup = document.getElementById('installation-type-group');
            const typeError = document.getElementById('error-installation_type');
            if (typeGroup) typeGroup.classList.add('has-error');
            if (typeError) typeError.classList.add('visible');
            errors.push('Installation Type: Please select Residential or Business.');
        }

        // 2. Service (only once a type has unlocked the list)
        const serviceId = document.getElementById('service_id');
        if (getInstallationType() && (!serviceId || !serviceId.value)) {
            setFieldError('service_id', 'error-service_id', 'Please select a service package.');
            errors.push('Service: Please select a service package.');
        }

        // 3. Purok / Street & Landmark
        const purok = document.getElementById('purok_landmark');
        if (!purok || !purok.value.trim()) {
            setFieldError('purok_landmark', 'error-purok_landmark', 'Please enter your purok / street and a nearby landmark.');
            errors.push('Purok / Street & Landmark: Please describe your exact location.');
        }

        // 4. Preferred Date
        const prefDate = document.getElementById('preferred_date');
        if (!prefDate || !prefDate.value) {
            setFieldError('preferred_date', 'error-preferred_date', 'Please select a preferred date.');
            errors.push('Preferred Date: Please select a date.');
        }

        // 5. Time Slot
        const slotSelected = document.querySelector('input[name="preferred_time"]:checked');
        if (!slotSelected) {
            const slotsContainer = document.getElementById('slots-container');
            const slotsError = document.getElementById('error-preferred_time');
            if (slotsContainer) slotsContainer.classList.add('has-error');
            if (slotsError) slotsError.classList.add('visible');
            errors.push('Time Slot: Please select an available time slot.');
        }

        // 6. Reference Number
        const refNum = document.getElementById('reference_number');
        if (!refNum || !refNum.value.trim()) {
            setFieldError('reference_number', 'error-reference_number', 'Please enter your payment reference/transaction number.');
            errors.push('Reference Number: Please enter your payment reference or transaction number.');
        } else if (!/^\d+$/.test(refNum.value.trim())) {
            setFieldError('reference_number', 'error-reference_number', 'Reference number must contain numbers only.');
            errors.push('Reference Number: Reference number must contain numbers only.');
        }

        // 7. Payment Proof
        const payProof = document.getElementById('payment_proof');
        if (!payProof || !payProof.files || payProof.files.length === 0) {
            setFieldError('payment_proof', 'error-payment_proof', 'Please upload a vertical screenshot of your GCash receipt.');
            errors.push('Payment Receipt: Please upload a vertical screenshot of your GCash receipt.');
        } else if (!isPaymentProofValid) {
            setFieldError('payment_proof', 'error-payment_proof', 'Please upload a valid vertical (portrait) phone screenshot of your GCash receipt.');
            errors.push('Payment Receipt: Invalid screenshot format or orientation.');
        }

        return errors;
    }

    function handleFormSubmit() {
        const errors = validateBookingForm();

        if (errors.length > 0) {
            showBanner(errors);
            return; // Block submission
        }

        // All valid — disable button and submit
        const btn = document.getElementById('submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                Submitting...
            `;
            btn.style.background = '#64748b';
            btn.style.cursor = 'not-allowed';
        }

        document.getElementById('booking-form').submit();
    }

    /* Auto-clear per-field error on change/input */
    document.addEventListener('DOMContentLoaded', function () {
        const fieldMap = [
            ['service_id',       'error-service_id'],
            ['purok_landmark',   'error-purok_landmark'],
            ['preferred_date',   'error-preferred_date'],
            ['reference_number', 'error-reference_number'],
            ['payment_proof',    'error-payment_proof'],
        ];
        fieldMap.forEach(([fid, eid]) => {
            const el = document.getElementById(fid);
            if (el) {
                el.addEventListener('change', () => clearFieldError(fid, eid));
                el.addEventListener('input',  () => clearFieldError(fid, eid));
            }
        });

        const refInput = document.getElementById('reference_number');
        if (refInput) {
            refInput.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key) && e.key !== 'Enter') {
                    e.preventDefault();
                }
            });
            refInput.addEventListener('paste', function(e) {
                const paste = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                if (/\D/.test(paste)) {
                    e.preventDefault();
                    const clean = paste.replace(/\D/g, '');
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.substring(0, start) + clean + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = start + clean.length;
                    this.dispatchEvent(new Event('input'));
                }
            });
        }

        isPaymentProofValid = false;
        const payProofInput = document.getElementById('payment_proof');
        if (payProofInput) {
            payProofInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                const preview = document.getElementById('payment_proof_preview');
                const img = document.getElementById('payment_proof_img');
                const nameEl = document.getElementById('payment_proof_name');
                const metaEl = document.getElementById('payment_proof_meta');

                if (!file) {
                    isPaymentProofValid = false;
                    if (preview) preview.style.display = 'none';
                    return;
                }

                // File size checks (15 KB to 4 MB)
                if (file.size < 15 * 1024) {
                    isPaymentProofValid = false;
                    this.value = '';
                    if (preview) preview.style.display = 'none';
                    setFieldError('payment_proof', 'error-payment_proof', 'The file is too small to be a valid screenshot. Please upload a full GCash receipt.');
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    isPaymentProofValid = false;
                    this.value = '';
                    if (preview) preview.style.display = 'none';
                    setFieldError('payment_proof', 'error-payment_proof', 'File size exceeds 4MB. Please upload a smaller image.');
                    return;
                }

                // Image dimensions and orientation check
                const objectUrl = URL.createObjectURL(file);
                const tempImg = new Image();
                tempImg.onload = function () {
                    const width = tempImg.naturalWidth;
                    const height = tempImg.naturalHeight;
                    URL.revokeObjectURL(objectUrl);

                    // Require portrait orientation: height must be strictly greater than width
                    if (height <= width) {
                        isPaymentProofValid = false;
                        payProofInput.value = '';
                        if (preview) preview.style.display = 'none';
                        setFieldError('payment_proof', 'error-payment_proof', 'Please upload a vertical (portrait) phone screenshot of your GCash receipt, not a landscape photo.');
                        return;
                    }

                    // Minimum resolution: 300x500
                    if (width < 300 || height < 500) {
                        isPaymentProofValid = false;
                        payProofInput.value = '';
                        if (preview) preview.style.display = 'none';
                        setFieldError('payment_proof', 'error-payment_proof', 'Image resolution is too low (' + width + '×' + height + ' px). Please upload a full-resolution screenshot.');
                        return;
                    }

                    // All checks passed!
                    isPaymentProofValid = true;
                    clearFieldError('payment_proof', 'error-payment_proof');

                    if (preview && img && nameEl && metaEl) {
                        img.src = URL.createObjectURL(file);
                        nameEl.textContent = file.name;
                        metaEl.textContent = width + ' × ' + height + ' px · ' + (file.size / 1024).toFixed(1) + ' KB';
                        preview.style.display = 'flex';
                    }
                };

                tempImg.onerror = function () {
                    URL.revokeObjectURL(objectUrl);
                    isPaymentProofValid = false;
                    payProofInput.value = '';
                    if (preview) preview.style.display = 'none';
                    setFieldError('payment_proof', 'error-payment_proof', 'Could not read image file. Please upload a valid JPG, PNG, or WebP screenshot.');
                };

                tempImg.src = objectUrl;
            });
        }

        document.querySelectorAll('input[name="preferred_time"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const slotsContainer = document.getElementById('slots-container');
                const slotsError    = document.getElementById('error-preferred_time');
                if (slotsContainer) slotsContainer.classList.remove('has-error');
                if (slotsError)    slotsError.classList.remove('visible');
            });
        });
    });

    /* ══════════════════════════════════════════════
       PAYMENT ACCOUNTS CONFIG
    ══════════════════════════════════════════════ */
    const PAYMENT_ACCOUNTS = {
        'GCash': {
            badge: 'GCash E-Wallet',
            badgeBg: '#dbeafe',
            badgeFg: '#1d4ed8',
            name: 'JO*Y M.',
            accountNumber: '+63 985 838 ****',
            typeLabel: 'GCash Number',
            qrImage: @json(asset('assets/images/gcash-admin-qr.png') . '?v=3bc4c37b'),
            instructions: 'Scan the administrator\'s official GCash QR code and send the exact payment. Enter your Name or Account No. in the message/notes field.'
        },
    };

    document.addEventListener('DOMContentLoaded', function() {
        updatePaymentInstructions();
        
        document.getElementById('preferred_date').addEventListener('change', function() {
            let selectedDate = this.value;
            let serviceId = document.getElementById('service_id').value;
            let url = new URL(window.location.href);
            url.searchParams.set('date', selectedDate);
            if (serviceId) {
                url.searchParams.set('service_id', serviceId);
            }
            const chosenType = getInstallationType();
            if (chosenType) {
                url.searchParams.set('installation_type', chosenType);
            }
            window.location.href = url.toString();
        });

        initInstallationType();
    });

    /* ── Installation type gates the service list ── */
    function getInstallationType() {
        const picked = document.querySelector('input[name="installation_type"]:checked');
        return picked ? picked.value : '';
    }

    function applyServiceFilter() {
        const select = document.getElementById('service_id');
        if (!select) return;

        const type = getInstallationType();
        const placeholder = select.querySelector('option[value=""]');
        let visibleCount = 0;

        Array.prototype.forEach.call(select.options, function (opt) {
            if (opt.value === '') return;

            const accountType = opt.dataset.accountType || 'both';
            const matches = !!type && (accountType === 'both' || accountType === type);

            // `hidden` alone still leaves the option reachable with a keyboard in
            // some browsers, so disable it as well.
            opt.hidden = !matches;
            opt.disabled = !matches;
            if (matches) visibleCount++;
        });

        if (placeholder) {
            placeholder.textContent = type
                ? (visibleCount ? select.dataset.placeholderEmpty : 'No packages available for this type')
                : select.dataset.placeholderLocked;
        }

        // Drop a selection that the new type no longer offers.
        const current = select.selectedOptions[0];
        if (current && current.value !== '' && current.disabled) {
            select.value = '';
        }

        select.disabled = !type;
    }

    function initInstallationType() {
        const group = document.getElementById('installation-type-group');
        if (!group) return;

        group.addEventListener('change', function (e) {
            if (e.target.name !== 'installation_type') return;

            group.querySelectorAll('.install-type-card').forEach(function (card) {
                card.classList.toggle('is-selected', card.contains(e.target));
            });
            group.classList.remove('has-error');
            const err = document.getElementById('error-installation_type');
            if (err) err.classList.remove('visible');

            applyServiceFilter();
        });

        applyServiceFilter();
    }

    function updatePaymentInstructions() {
        const pmSelect = document.getElementById('payment_method');
        if (!pmSelect) return;

        let selectedVal = pmSelect.value;
        let details = PAYMENT_ACCOUNTS[selectedVal] || PAYMENT_ACCOUNTS['GCash'];

        const badgeEl = document.getElementById('instruction_provider_badge');
        if (badgeEl) {
            badgeEl.innerText = details.badge;
            badgeEl.style.background = details.badgeBg;
            badgeEl.style.color = details.badgeFg;
        }

        const contentEl = document.getElementById('account_details_content');
        if (contentEl) {
            contentEl.innerHTML = `
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem;">${details.badge} Official Account</div>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: #475569;">Account Name:</div>
                        <strong style="font-size: 1rem; color: var(--text-dark);">${details.name}</strong>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.8rem; color: #475569;">${details.typeLabel}:</div>
                        <strong style="font-size: 1.15rem; color: #dc2626; font-family: monospace;">${details.accountNumber}</strong>
                    </div>
                </div>
                <div style="font-size: 0.82rem; color: #475569; border-top: 1px dashed var(--border-light); padding-top: 0.5rem; margin-top: 0.5rem;">
                    <strong>Instructions:</strong> ${details.instructions}
                </div>
                <div style="border-top: 1px dashed var(--border-light); padding-top: 1rem; margin-top: 1rem; text-align: center;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.65rem;">Scan to pay with GCash</div>
                    <img src="${details.qrImage}"
                         alt="Official administrator GCash QR code"
                         style="display: block; width: min(100%, 360px); height: auto; margin: 0 auto; border-radius: 12px; border: 1px solid var(--border-light);"
                         loading="eager">
                    <small style="display: block; color: var(--text-muted); margin-top: 0.55rem;">Confirm that the recipient is <strong>${details.name}</strong> before sending.</small>
                </div>
            `;
        }
    }


</script>
@endpush
