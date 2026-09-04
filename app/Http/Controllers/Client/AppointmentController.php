<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\Notification;
use App\Models\ClientPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        $appointments = Appointment::with('service')
            ->where('client_id', $client->id)
            ->orderBy('preferred_date', 'desc')
            ->orderBy('preferred_time', 'desc')
            ->get();

        return view('client.appointments.index', compact('appointments'));
    }

    public function create(Request $request)
    {
        $client = Auth::guard('client')->user();
        $preselectedServiceId = $request->get('service_id', 0);
        $selectedDate = $request->get('date', date('Y-m-d', strtotime('+1 day')));

        if (strtotime($selectedDate) < strtotime(date('Y-m-d'))) {
            $selectedDate = date('Y-m-d', strtotime('+1 day'));
        }

        $services = Service::active()->orderBy('service_name')->get();
        $allSlots = TimeSlot::available()->pluck('slot_time')->toArray();

        // Fetch booked slots for the selected date
        $bookedSlots = Appointment::where('preferred_date', $selectedDate)
            ->where('status', '!=', 'cancelled')
            ->pluck('preferred_time')
            ->toArray();

        // Client saved payment methods
        $paymentMethods = ClientPaymentMethod::where('client_id', $client->id)
            ->orderBy('is_default', 'desc')
            ->get();
        $defaultMethod = $paymentMethods->firstWhere('is_default', true) ?? $paymentMethods->first();

        return view('client.appointments.book', compact(
            'services', 'allSlots', 'bookedSlots', 'selectedDate', 'preselectedServiceId',
            'paymentMethods', 'defaultMethod'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'preferred_date'   => 'required|date|after_or_equal:today',
            'preferred_time'   => 'required',
            'payment_method'   => 'required|string|max:50|not_in:Cash,cash',
            'reference_number' => 'nullable|string|max:100',
            'payment_proof'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'payment_method.not_in' => 'Cash payments are not available on the client portal. Please choose a digital payment method (GCash, Maya, Bank Transfer, or Card).',
        ]);

        $client = Auth::guard('client')->user();

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        }

        $paymentMethod = $request->input('payment_method', 'GCash');

        // Check for conflict
        if (Appointment::hasConflict($request->preferred_date, $request->preferred_time)) {
            // Auto-reschedule: find next available slot
            $next = $this->findNextAvailableSlot($request->preferred_date, $request->preferred_time);

            if ($next) {
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

                // Create notification for admin
                Notification::create([
                    'for_admin' => true,
                    'title'     => 'New Booking (Auto-Rescheduled)',
                    'message'   => "{$client->firstname} {$client->lastname} booked (rescheduled to {$next['date']} at " . date('g:i A', strtotime($next['time'])) . ")",
                    'link'      => 'admin/appointments',
                ]);

                return redirect()->route('client.appointments')
                    ->with('success_message', "Your original slot was taken. You have been auto-rescheduled to {$next['date']} at " . date('g:i A', strtotime($next['time'])) . ".");
            }

            return back()->withErrors(['preferred_time' => 'No available slots found within the next 14 days. Please try different dates.'])->withInput();
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

        // Create notification for admin
        Notification::create([
            'for_admin' => true,
            'title'     => 'New Booking Request',
            'message'   => "{$client->firstname} {$client->lastname} booked {$request->preferred_date} at " . date('g:i A', strtotime($request->preferred_time)),
            'link'      => 'admin/appointments',
        ]);

        return redirect()->route('client.appointments')
            ->with('success_message', 'Your appointment has been booked successfully! Please wait for admin confirmation.');
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $request->validate([
            'payment_method'   => 'required|string|max:50|not_in:Cash,cash',
            'reference_number' => 'nullable|string|max:100',
            'payment_proof'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'payment_method.not_in' => 'Cash is not available. Please select a digital payment method.',
        ]);

        $client = Auth::guard('client')->user();
        $appointment = Appointment::where('client_id', $client->id)->findOrFail($id);

        $data = [
            'payment_method'   => $request->payment_method,
            'reference_number' => $request->input('reference_number'),
        ];

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof'] = $request->file('payment_proof')->store('payments', 'public');
        }

        $appointment->update($data);

        return redirect()->route('client.appointments')
            ->with('success_message', 'Payment method updated successfully for Appointment #' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT) . '.');
    }

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

    public function getBookedSlots(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $bookedSlots = Appointment::where('preferred_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('preferred_time')
            ->toArray();

        return response()->json($bookedSlots);
    }
}
