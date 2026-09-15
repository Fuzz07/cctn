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
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        padding: 2rem;
        border: 1px solid #e2e8f0;
    }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-weight: 700; margin-bottom: 0.5rem; color: #1e293b; font-size: 0.9rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }

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
        border: 1.5px solid #e2e8f0;
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

            <!-- Service Selection -->
            <div class="form-group">
                <label class="form-label" for="service_id">Select Service <span style="color:#dc2626">*</span></label>
                <select name="service_id" id="service_id" class="form-control {{ $errors->has('service_id') ? 'is-invalid' : '' }}">
                    <option value="">Choose a service package...</option>
                    @foreach ($services as $serv)
                        <option value="{{ $serv->id }}" {{ (old('service_id', $preselectedServiceId) == $serv->id) ? 'selected' : '' }}>
                            {{ $serv->service_name }} - ₱{{ number_format($serv->price, 2) }}
                        </option>
                    @endforeach
                </select>
                <span class="field-error {{ $errors->has('service_id') ? 'visible' : '' }}" id="error-service_id">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('service_id', 'Please select a service package.') }}
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
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f9fafb; border: 1.5px solid #e2e8f0; border-radius: 3px;"></span> Available Slot
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
            <div class="form-group" style="background: #fafafa; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                <label class="form-label" for="payment_method" style="display: flex; align-items: center; gap: 0.5rem; color: #0f172a; margin-bottom: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Select Digital Payment Method <span style="color:#dc2626">*</span>
                </label>

                <select name="payment_method" id="payment_method" class="form-control" required style="font-weight: 600;" onchange="updatePaymentInstructions()">
                    <option value="GCash" {{ old('payment_method', 'GCash') == 'GCash' ? 'selected' : '' }}>GCash (E-Wallet)</option>
                    <option value="Maya" {{ old('payment_method', '') == 'Maya' ? 'selected' : '' }}>Maya (E-Wallet)</option>
                    <option value="Bank Transfer" {{ old('payment_method', '') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer (BDO, BPI, UnionBank)</option>
                    <option value="Credit/Debit Card" {{ old('payment_method', '') == 'Credit/Debit Card' ? 'selected' : '' }}>Credit / Debit Card</option>
                </select>

                <!-- DYNAMIC PAYMENT INSTRUCTIONS CARD -->
                <div id="payment_instructions_box" style="margin-top: 1.25rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: #dc2626; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                            <i class="bi bi-info-circle-fill"></i> Payment Account Instructions
                        </span>
                        <span id="instruction_provider_badge" style="background: #dbeafe; color: #1d4ed8; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.65rem; border-radius: 99px;">
                            GCash
                        </span>
                    </div>

                    <!-- Dynamic Account Info Display -->
                    <div id="account_details_content" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <!-- Injected via JavaScript -->
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" for="reference_number" style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                            Reference / Transaction Number <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="reference_number" id="reference_number"
                               class="form-control {{ $errors->has('reference_number') ? 'is-invalid' : '' }}"
                               placeholder="e.g. 10029384756"
                               value="{{ old('reference_number') }}"
                               style="font-family: monospace; font-size: 0.95rem; font-weight: 700;">
                        <span class="field-error {{ $errors->has('reference_number') ? 'visible' : '' }}" id="error-reference_number">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $errors->first('reference_number', 'Please enter your payment reference/transaction number.') }}
                        </span>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.25rem;">Enter the reference or reference ID from your payment confirmation screen.</small>
                    </div>

                    <div>
                        <label class="form-label" for="payment_proof" style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                            Upload Payment Receipt or Screenshot <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="file" name="payment_proof" id="payment_proof"
                               class="form-control {{ $errors->has('payment_proof') ? 'is-invalid' : '' }}"
                               accept="image/jpeg,image/png,image/jpg,image/webp">
                        <span class="field-error {{ $errors->has('payment_proof') ? 'visible' : '' }}" id="error-payment_proof">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $errors->first('payment_proof', 'Please upload a screenshot or photo of your payment receipt.') }}
                        </span>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.25rem;">Upload a clear screenshot or photo of your payment receipt.</small>
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
@endsection

@push('scripts')
<script>
    /* ══════════════════════════════════════════════
       CLIENT-SIDE FORM VALIDATION
    ══════════════════════════════════════════════ */

    function setFieldError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorEl = document.getElementById(errorId);
        if (field) field.classList.add('is-invalid');
        if (errorEl) {
            if (message) errorEl.querySelector('svg').nextSibling
                ? errorEl.lastChild.textContent = ' ' + message
                : (errorEl.textContent = message);
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

        // 1. Service
        const serviceId = document.getElementById('service_id');
        if (!serviceId || !serviceId.value) {
            setFieldError('service_id', 'error-service_id', 'Please select a service package.');
            errors.push('Service: Please select a service package.');
        }

        // 2. Preferred Date
        const prefDate = document.getElementById('preferred_date');
        if (!prefDate || !prefDate.value) {
            setFieldError('preferred_date', 'error-preferred_date', 'Please select a preferred date.');
            errors.push('Preferred Date: Please select a date.');
        }

        // 3. Time Slot
        const slotSelected = document.querySelector('input[name="preferred_time"]:checked');
        if (!slotSelected) {
            const slotsContainer = document.getElementById('slots-container');
            const slotsError = document.getElementById('error-preferred_time');
            if (slotsContainer) slotsContainer.classList.add('has-error');
            if (slotsError) slotsError.classList.add('visible');
            errors.push('Time Slot: Please select an available time slot.');
        }

        // 4. Reference Number
        const refNum = document.getElementById('reference_number');
        if (!refNum || !refNum.value.trim()) {
            setFieldError('reference_number', 'error-reference_number', 'Please enter your payment reference/transaction number.');
            errors.push('Reference Number: Please enter your payment reference or transaction number.');
        }

        // 5. Payment Proof
        const payProof = document.getElementById('payment_proof');
        if (!payProof || !payProof.files || payProof.files.length === 0) {
            setFieldError('payment_proof', 'error-payment_proof', 'Please upload a screenshot or photo of your payment receipt.');
            errors.push('Payment Receipt: Please upload a screenshot or photo of your payment receipt.');
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
            name: 'Bogo Cable Television Inc. (BCTVI)',
            accountNumber: '0917 888 2099',
            typeLabel: 'GCash Number',
            instructions: 'Send exact payment to the GCash account above. Enter your Name or Account No. in the message/notes field.'
        },
        'Maya': {
            badge: 'Maya E-Wallet',
            badgeBg: '#dcfce7',
            badgeFg: '#15803d',
            name: 'Bogo Cable Television Inc. (BCTVI)',
            accountNumber: '0917 888 2099',
            typeLabel: 'Maya Number',
            instructions: 'Transfer exact amount to the official Maya account details above.'
        },
        'Bank Transfer': {
            badge: 'Bank Transfer (BDO / BPI)',
            badgeBg: '#f3e8ff',
            badgeFg: '#6b21a8',
            name: 'Bogo Cable Television Inc.',
            accountNumber: '0012-3456-7890 (BDO Unibank) / 1234-5678-90 (BPI)',
            typeLabel: 'Bank Account Number',
            instructions: 'Execute online bank transfer or over-the-counter deposit to BCTVI BDO/BPI account.'
        },
        'Credit/Debit Card': {
            badge: 'Credit / Debit Card',
            badgeBg: '#e0f2fe',
            badgeFg: '#0369a1',
            name: 'BCTVI Payment Channel',
            accountNumber: 'Online Card Portal Transfer',
            typeLabel: 'Payment Channel',
            instructions: 'Process your card payment via our verified online payment account channel.'
        }
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
            window.location.href = url.toString();
        });
    });

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
                <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem;">${details.badge} Official Account</div>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: #475569;">Account Name:</div>
                        <strong style="font-size: 1rem; color: #0f172a;">${details.name}</strong>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.8rem; color: #475569;">${details.typeLabel}:</div>
                        <strong style="font-size: 1.15rem; color: #dc2626; font-family: monospace;">${details.accountNumber}</strong>
                    </div>
                </div>
                <div style="font-size: 0.82rem; color: #475569; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; margin-top: 0.5rem;">
                    <strong>Instructions:</strong> ${details.instructions}
                </div>
            `;
        }
    }


</script>
@endpush
