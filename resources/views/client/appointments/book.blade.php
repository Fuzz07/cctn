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

        <form action="{{ route('client.book.submit') }}" method="POST" id="booking-form">
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
                            $formatted_time = date('h:i A', strtotime($slot_time));
                        @endphp
                        <div>
                            <input type="radio" name="preferred_time" id="slot_{{ $slot_time }}" 
                                   value="{{ $slot_time }}" class="slot-radio" 
                                   {{ $is_booked ? 'disabled' : '' }} required>
                            
                            <label for="slot_{{ $slot_time }}" 
                                   class="slot-label {{ $is_booked ? 'unavailable' : '' }}">
                                {{ $formatted_time }}
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
                        <span style="display: inline-block; width: 12px; height: 12px; background: #dc2626; border-radius: 3px;"></span> Selected Slot
                    </span>
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f3f4f6; opacity: 0.4; border: 1.5px solid #e5e7eb; border-radius: 3px;"></span> Booked (Unavailable)
                    </span>
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
</script>
@endpush
