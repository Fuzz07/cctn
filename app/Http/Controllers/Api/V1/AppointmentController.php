<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // ─── GET /api/v1/appointments ────────────────────────────────────────────
    public function index(Request $request)
    {
        $client = $request->user();

        $appointments = Appointment::with('service')
            ->where('client_id', $client->id)
            ->orderBy('preferred_date', 'desc')
            ->orderBy('preferred_time', 'desc')
            ->get();

        return response()->json([
            'success'      => true,
            'appointments' => AppointmentResource::collection($appointments),
        ]);
    }

    // ─── GET /api/v1/appointments/slots?date=YYYY-MM-DD ──────────────────────
    public function slots(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        $allSlots = TimeSlot::available()->pluck('slot_time')->toArray();

        $bookedSlots = Appointment::where('preferred_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('preferred_time')
            ->map(fn($t) => date('H:i', strtotime($t)))
            ->toArray();

        $slots = array_map(function ($slot) use ($bookedSlots) {
            return [
                'time'      => date('H:i', strtotime($slot)),
                'label'     => date('g:i A', strtotime($slot)),
                'available' => !in_array(date('H:i', strtotime($slot)), $bookedSlots),
            ];
        }, $allSlots);

        return response()->json([
            'success' => true,
            'date'    => $date,
            'slots'   => $slots,
        ]);
    }

    // ─── POST /api/v1/appointments ───────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'preferred_date'   => 'required|date|after_or_equal:today',
            'preferred_time'   => 'required|string',
            'message'          => 'nullable|string|max:500',
            'payment_method'   => 'nullable|string|max:50|not_in:Cash,cash',
            'reference_number' => 'nullable|string|max:100',
            'payment_proof'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'payment_method.not_in' => 'Cash is not available. Please select a digital payment method (GCash, Maya, Bank Transfer, or Card).',
        ]);

        $client = $request->user();

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        }

        $defaultMethod = $client->defaultPaymentMethod?->provider_name;
        $paymentMethod = $request->input('payment_method') ?: ($defaultMethod ?: 'GCash');

        // Check conflict → auto-reschedule
        if (Appointment::hasConflict($request->preferred_date, $request->preferred_time)) {
            $next = $this->findNextAvailableSlot($request->preferred_date, $request->preferred_time);

            if (!$next) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available slots found within the next 14 days.',
                ], 422);
            }

            $appointment = Appointment::create([
                'client_id'        => $client->id,
                'service_id'       => $request->service_id,
                'preferred_date'   => $next['date'],
                'preferred_time'   => $next['time'],
                'message'          => $request->input('message', ''),
                'status'           => 'pending',
                'payment_method'   => $paymentMethod,
                'reference_number' => $request->input('reference_number'),
                'payment_proof'    => $paymentProofPath,
            ]);

            Notification::create([
                'for_admin' => true,
                'title'     => 'New Booking (Auto-Rescheduled)',
                'message'   => "{$client->firstname} {$client->lastname} booked via app (rescheduled to {$next['date']} at " . date('g:i A', strtotime($next['time'])) . ")",
                'link'      => 'admin/appointments',
            ]);

            return response()->json([
                'success'       => true,
                'rescheduled'   => true,
                'message'       => "Your slot was taken. Auto-rescheduled to {$next['date']} at " . date('g:i A', strtotime($next['time'])) . '.',
                'appointment'   => new AppointmentResource($appointment->load('service')),
            ], 201);
        }

        $appointment = Appointment::create([
            'client_id'        => $client->id,
            'service_id'       => $request->service_id,
            'preferred_date'   => $request->preferred_date,
            'preferred_time'   => $request->preferred_time,
            'message'          => $request->input('message', ''),
            'status'           => 'pending',
            'payment_method'   => $paymentMethod,
            'reference_number' => $request->input('reference_number'),
            'payment_proof'    => $paymentProofPath,
        ]);

        Notification::create([
            'for_admin' => true,
            'title'     => 'New Booking Request (App)',
            'message'   => "{$client->firstname} {$client->lastname} booked via app on {$request->preferred_date} at " . date('g:i A', strtotime($request->preferred_time)),
            'link'      => 'admin/appointments',
        ]);

        return response()->json([
            'success'     => true,
            'rescheduled' => false,
            'message'     => 'Appointment booked successfully! Awaiting admin confirmation.',
            'appointment' => new AppointmentResource($appointment->load('service')),
        ], 201);
    }

    // ─── POST /api/v1/appointments/{id}/payment-method ──────────────────────
    public function updatePaymentMethod(Request $request, int $id)
    {
        $request->validate([
            'payment_method'   => 'required|string|max:50|not_in:Cash,cash',
            'reference_number' => 'nullable|string|max:100',
            'payment_proof'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'payment_method.not_in' => 'Cash is not available. Please select a digital payment method.',
        ]);

        $client      = $request->user();
        $appointment = Appointment::where('client_id', $client->id)->findOrFail($id);

        $data = [
            'payment_method'   => $request->payment_method,
            'reference_number' => $request->input('reference_number'),
        ];

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof')->store('payments', 'public');
        }

        $appointment->update($data);

        return response()->json([
            'success'     => true,
            'message'     => 'Payment method updated successfully.',
            'appointment' => new AppointmentResource($appointment->load('service')),
        ]);
    }

    // ─── DELETE /api/v1/appointments/{id} ────────────────────────────────────
    public function destroy(Request $request, int $id)
    {
        $client      = $request->user();
        $appointment = Appointment::where('client_id', $client->id)->findOrFail($id);

        if ($appointment->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approved appointments cannot be cancelled.',
            ], 422);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function findNextAvailableSlot(string $startDate, string $preferredTime, int $maxDays = 14): ?array
    {
        $activeSlots = TimeSlot::available()->pluck('slot_time')->toArray();
        if (empty($activeSlots)) return null;

        for ($d = 0; $d < $maxDays; $d++) {
            $checkDate = date('Y-m-d', strtotime("+{$d} days", strtotime($startDate)));
            if (strtotime($checkDate) < strtotime(date('Y-m-d'))) continue;

            $booked = Appointment::where('preferred_date', $checkDate)
                ->where('status', '!=', 'cancelled')
                ->pluck('preferred_time')
                ->toArray();

            foreach ($activeSlots as $slot) {
                if ($d === 0 && $slot < $preferredTime) continue;
                if (in_array($slot, $booked)) continue;
                return ['date' => $checkDate, 'time' => $slot];
            }
        }

        return null;
    }
}
