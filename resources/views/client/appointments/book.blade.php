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

        <form action="{{ route('client.book.submit') }}" method="POST" id="booking-form" enctype="multipart/form-data">
            @csrf

            <!-- Service Selection -->
            <div class="form-group">
                <label class="form-label" for="service_id">Select Service *</label>
                <select name="service_id" id="service_id" class="form-control" required>
                    <option value="">Choose a service package...</option>
                    @foreach ($services as $serv)
                        <option value="{{ $serv->id }}" {{ (old('service_id', $preselectedServiceId) == $serv->id) ? 'selected' : '' }}>
                            {{ $serv->service_name }} - ₱{{ number_format($serv->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Selection (reloads page to fetch timeslots) -->
            <div class="form-group">
                <label class="form-label" for="preferred_date">Preferred Date *</label>
                <input type="date" name="preferred_date" id="preferred_date" class="form-control" 
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                       value="{{ old('preferred_date', $selectedDate) }}" 
                       required>
                <p class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem;">Changing the date automatically updates the list of available slots below.</p>
            </div>

            <!-- Time Slot Radio Grid -->
            <div class="form-group">
                <label class="form-label">Available Time Slots for <span class="text-gold" id="display-date">{{ date('F d, Y', strtotime($selectedDate)) }}</span> *</label>
                
                <div class="slots-container" id="slots-container">
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <label class="form-label" for="payment_method" style="margin: 0; display: flex; align-items: center; gap: 0.5rem; color: #0f172a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        Select Digital Payment Method *
                    </label>
                    <a href="{{ route('client.payment-methods') }}" target="_blank" style="font-size: 0.78rem; font-weight: 700; color: #dc2626; text-decoration: none;">
                        + Manage Saved Methods
                    </a>
                </div>

                @if(isset($paymentMethods) && $paymentMethods->count() > 0)
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                            Your Saved Payment Methods
                        </span>
                        <div style="display: grid; gap: 0.5rem;">
                            @foreach($paymentMethods as $pm)
                                <label style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border: 1px solid {{ $pm->is_default ? '#fca5a5' : '#e2e8f0' }}; background: {{ $pm->is_default ? '#fff5f5' : '#f8fafc' }}; border-radius: 6px; cursor: pointer;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="radio" name="saved_payment_method_id" value="{{ $pm->id }}" {{ $pm->is_default ? 'checked' : '' }} onchange="selectSavedMethod('{{ $pm->provider_name }}', '{{ $pm->account_number }}')" style="accent-color: #dc2626;">
                                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">{{ $pm->provider_name }}</span>
                                        <span style="font-size: 0.8rem; color: #64748b;">({{ $pm->masked_account_number }})</span>
                                    </div>
                                    @if($pm->is_default)
                                        <span style="font-size: 0.7rem; font-weight: 800; background: #dc2626; color: #fff; padding: 0.15rem 0.45rem; border-radius: 4px;">Default</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <select name="payment_method" id="payment_method" class="form-control" required style="font-weight: 600;" onchange="updatePaymentInstructions()">
                    <option value="GCash" {{ old('payment_method', ($defaultMethod->provider_name ?? 'GCash')) == 'GCash' ? 'selected' : '' }}>GCash (E-Wallet)</option>
                    <option value="Maya" {{ old('payment_method', ($defaultMethod->provider_name ?? '')) == 'Maya' ? 'selected' : '' }}>Maya (E-Wallet)</option>
                    <option value="Bank Transfer" {{ old('payment_method', ($defaultMethod->provider_name ?? '')) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer (BDO, BPI, UnionBank)</option>
                    <option value="Credit/Debit Card" {{ old('payment_method', ($defaultMethod->provider_name ?? '')) == 'Credit/Debit Card' ? 'selected' : '' }}>Credit / Debit Card</option>
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
                        <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="e.g. 10029384756" value="{{ old('reference_number') }}" style="font-family: monospace; font-size: 0.95rem; font-weight: 700;">
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.25rem;">Enter the reference or reference ID from your payment confirmation screen.</small>
                    </div>

                    <div>
                        <label class="form-label" for="payment_proof" style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                            Upload Payment Receipt or Screenshot <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="file" name="payment_proof" id="payment_proof" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
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

            <button type="submit" class="btn-submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Confirm Appointment Booking
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    function selectSavedMethod(provider, accountNum) {
        const pmSelect = document.getElementById('payment_method');
        if (!pmSelect) return;
        
        let matched = false;
        for (let i = 0; i < pmSelect.options.length; i++) {
            if (pmSelect.options[i].value.toLowerCase() === provider.toLowerCase() || 
                pmSelect.options[i].text.toLowerCase().includes(provider.toLowerCase())) {
                pmSelect.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched && provider.toLowerCase().includes('bank')) {
            pmSelect.value = 'Bank Transfer';
        }
        updatePaymentInstructions();
    }
</script>
@endpush
