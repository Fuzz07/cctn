<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filterStatus  = $request->get('status', 'all');
        $filterService = $request->get('service_id', 0);
        $filterDate    = $request->get('date', '');
        $filterSearch  = trim((string) $request->get('search', ''));
        $filterType    = $request->get('installation_type', 'all');

        $query = Appointment::with(['client', 'service']);

        if ($filterSearch !== '') {
            $terms = preg_split('/\s+/', $filterSearch, -1, PREG_SPLIT_NO_EMPTY);

            $query->whereHas('client', function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('firstname', 'like', "%{$term}%")
                              ->orWhere('lastname', 'like', "%{$term}%");
                    });
                }
            });
        }

        if (in_array($filterType, ['residential', 'business'], true)) {
            $query->where('installation_type', $filterType);
        }

        if ($filterStatus !== 'all') {
            $query->where('status', $filterStatus);
        }
        if ($filterService > 0) {
            $query->where('service_id', $filterService);
        }
        if (!empty($filterDate)) {
            $query->where('preferred_date', $filterDate);
        }

        $sort = TableSort::resolve($request, [
            'ref'      => 'id',
            'client'   => function ($q, $dir) {
                return $q->orderBy(
                    Client::select('lastname')->whereColumn('clients.id', 'appointments.client_id'), $dir
                )->orderBy(
                    Client::select('firstname')->whereColumn('clients.id', 'appointments.client_id'), $dir
                );
            },
            'schedule' => ['preferred_date', 'preferred_time'],
            'status'   => 'status',
        ], 'schedule', 'desc');

        TableSort::apply($query, $sort);

        $appointments = $query->orderBy('id', 'desc')
            ->simplePaginate(7)
            ->appends($request->except(['page', 'manage_id']));

        $services = Service::orderBy('service_name')->get()->unique('service_name')->values();

        $manageAppointment = null;
        $manageId = $request->get('manage_id', 0);
        if ($manageId > 0) {
            $manageAppointment = Appointment::with(['client', 'service'])->find($manageId);
        }

        return view('admin.appointments.index', compact(
            'appointments', 'services', 'filterStatus', 'filterService',
            'filterDate', 'filterSearch', 'filterType', 'manageAppointment', 'sort'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'appointment_id'       => 'required|exists:appointments,id',
            'preferred_date'       => 'required|date',
            'preferred_time'       => 'required',
            'status'               => 'required|in:pending,approved,cancelled',
            'payment_status'       => 'nullable|string|in:Pending Payment,Payment Confirmed,Cancelled,paid,unpaid,pending',
            'subscription_ends_at' => 'nullable|date|after_or_equal:today',
        ]);

        $appointment = Appointment::with(['client', 'service'])->findOrFail($request->appointment_id);

        if ($request->status !== 'cancelled') {
            if (Appointment::hasConflict($request->preferred_date, $request->preferred_time, $appointment->id)) {
                return back()->withErrors(['preferred_time' => 'Scheduling Conflict: That slot is already booked.'])->withInput();
            }
        }

        $previousStatus        = $appointment->status;
        $previousPaymentStatus = $appointment->payment_status;
        $newPaymentStatus      = $request->input('payment_status');

        $updateData = [
            'preferred_date'       => $request->preferred_date,
            'preferred_time'       => $request->preferred_time,
            'status'               => $request->status,
            'admin_notes'          => $request->input('admin_notes', ''),
            'subscription_ends_at' => $request->input('subscription_ends_at'),
        ];

        if ($newPaymentStatus) {
            $updateData['payment_status'] = $newPaymentStatus;
            if (in_array($newPaymentStatus, ['Payment Confirmed', 'paid'], true) && !$appointment->payment_date) {
                $updateData['payment_date'] = now();
            }
        } elseif ($request->status === 'approved' && ($appointment->payment_method || $appointment->reference_number || $appointment->payment_proof)) {
            $updateData['payment_status'] = 'Payment Confirmed';
            if (!$appointment->payment_date) {
                $updateData['payment_date'] = now();
            }
        }

        $appointment->update($updateData);

        // Auto-create a Payment (sales revenue) record when newly approved
        $isNowApproved  = ($appointment->status === 'approved');
        $wasNotApproved = ($previousStatus !== 'approved');
        if ($isNowApproved && $wasNotApproved) {
            $this->createApprovalPayment($appointment);
        }

        // Notify client if cancelled
        if ($request->status === 'cancelled' && $previousStatus !== 'cancelled' && $appointment->client_id) {
            \App\Models\Notification::create([
                'for_admin' => false,
                'client_id' => $appointment->client_id,
                'title'     => 'Booking Cancelled',
                'message'   => 'Your appointment #' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT) . ' scheduled on ' . $appointment->preferred_date->format('F j, Y') . ' has been cancelled by BCTVI staff. Please contact us if you have questions.',
                'link'      => 'my-appointments',
            ]);
        }

        $isNowConfirmed  = in_array($appointment->payment_status, ['Payment Confirmed', 'paid'], true);
        $wasNotConfirmed = !in_array($previousPaymentStatus, ['Payment Confirmed', 'paid'], true);

        if (($isNowConfirmed && $wasNotConfirmed) || ($isNowApproved && $wasNotApproved) || ($isNowApproved && $isNowConfirmed)) {
            $mailErr = $this->sendPaymentConfirmationEmail($appointment);
            if ($mailErr) {
                session()->flash('email_warning', "Appointment updated, but confirmation email could not be sent: {$mailErr}");
            }
        }

        return redirect()->route('admin.appointments')
            ->with('success_message', "Appointment #{$appointment->id} updated successfully.");
    }

    public function quickUpdate(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status'         => 'required|in:approved,cancelled',
        ]);

        $appointment = Appointment::with(['client', 'service'])->findOrFail($request->appointment_id);

        $previousStatus = $appointment->status;

        $updateData = [
            'status'      => $request->status,
            'admin_notes' => $request->input('admin_notes', ''),
        ];

        if ($request->status === 'approved' && ($appointment->payment_method || $appointment->reference_number || $appointment->payment_proof)) {
            $updateData['payment_status'] = 'Payment Confirmed';
            if (!$appointment->payment_date) {
                $updateData['payment_date'] = now();
            }
        }

        $appointment->update($updateData);

        // Auto-create a Payment (sales revenue) record when newly approved
        if ($request->status === 'approved' && $previousStatus !== 'approved') {
            $this->createApprovalPayment($appointment);
        }

        if ($request->status === 'approved') {
            $mailErr = $this->sendPaymentConfirmationEmail($appointment);
            if ($mailErr) {
                session()->flash('email_warning', "Appointment approved, but confirmation email could not be sent: {$mailErr}");
            }
        }

        return redirect()->route('admin.appointments')
            ->with('success_message', "Appointment #{$appointment->id} status set to {$request->status}.");
    }

    /**
     * Show a printable receipt for an approved appointment.
     */
    public function receipt(int $id)
    {
        $appointment = Appointment::with(['client', 'service'])->findOrFail($id);

        // Find the linked payment if one was auto-generated
        $payment = Payment::where('notes', 'Booking #' . $id)
            ->orWhere(function ($q) use ($appointment) {
                $q->where('client_id', $appointment->client_id)
                  ->where('notes', 'like', '%Booking #' . $id . '%');
            })
            ->orderByDesc('id')
            ->first();

        return view('admin.appointments.receipt', compact('appointment', 'payment'));
    }

    /**
     * Auto-generate a Payment record when a booking is approved.
     * Uses the service price as the amount. Skips if a payment already exists for this booking.
     */
    private function createApprovalPayment(Appointment $appointment): void
    {
        try {
            $appointment->loadMissing(['client', 'service']);

            // Skip if already has a linked payment
            $alreadyExists = Payment::where('notes', 'like', '%Booking #' . $appointment->id . '%')
                ->exists();
            if ($alreadyExists) {
                return;
            }

            $admin       = Auth::guard('admin')->user();
            $service     = $appointment->service;
            $amountPaid  = $appointment->amount_paid > 0
                ? (float) $appointment->amount_paid
                : (float) ($service?->price ?? 0);

            if ($amountPaid <= 0) {
                return; // No amount to record
            }

            $receiptNo = 'APT-' . now()->format('Ymd') . '-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'client_id'        => $appointment->client_id,
                'account_number'   => $appointment->client?->account_number ?? '',
                'amount_paid'      => $amountPaid,
                'payment_method'   => $appointment->payment_method ?? 'Cash',
                'reference_number' => $appointment->reference_number ?? '',
                'received_by'      => $admin?->fullname ?? 'Admin Staff',
                'notes'            => 'Booking #' . $appointment->id . ' — ' . ($service?->service_name ?? 'Service'),
                'payment_date'     => $appointment->payment_date ?? now(),
                'receipt_no'       => $receiptNo,
            ]);

            // Store receipt_no back on the appointment if column exists
            $cols = Schema::getColumnListing('appointments');
            if (in_array('receipt_no', $cols, true)) {
                $appointment->update(['receipt_no' => $receiptNo]);
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                "Could not create approval payment for appointment #{$appointment->id}: " . $e->getMessage()
            );
        }
    }

    private function sendPaymentConfirmationEmail(Appointment $appointment): ?string
    {
        try {
            $appointment->loadMissing(['client', 'service']);
            $client = $appointment->client;
            if (!$client || empty($client->email)) {
                return 'Client has no email address on file.';
            }
            $client->notify(new \App\Notifications\AppointmentPaymentConfirmedNotification($appointment));
            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not send payment confirmation email for appointment #{$appointment->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return $e->getMessage();
        }
    }
}
