<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        Client::expireSubscriptions();

        $admin = Auth::guard('admin')->user();

        $stats = [
            'clients'  => Client::count(),
            'active_clients' => Client::active()->count(),
            'inactive_clients' => Client::inactive()->count(),
            'total'    => Appointment::count(),
            'pending'  => Appointment::where('status', 'pending')->count(),
            'approved' => Appointment::where('status', 'approved')->count(),
            'services' => Service::where('status', 'Active')->count(),
        ];

        $recentBookings = Appointment::with(['client.currentService', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $bookingTrend = $this->bookingTrend();
        $bookingsByPlan = $this->bookingsByPlan();
        $salesRevenueTrend = $this->salesRevenueTrend();

        return view('admin.dashboard', compact(
            'admin',
            'stats',
            'recentBookings',
            'bookingTrend',
            'bookingsByPlan',
            'salesRevenueTrend'
        ));
    }

    /**
     * Collected sales revenue per month over the last six months. Payments are
     * the sales ledger, so booking prices and unpaid amounts are not included.
     */
    private function salesRevenueTrend(): array
    {
        $firstMonth = Carbon::now()->startOfMonth()->subMonths(5);

        $payments = Payment::where('payment_date', '>=', $firstMonth)
            ->get(['amount_paid', 'payment_date']);

        $trend = ['labels' => [], 'values' => []];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $revenue = $payments
                ->filter(fn ($payment) => $payment->payment_date->isSameMonth($month))
                ->sum(fn ($payment) => (float) $payment->amount_paid);

            $trend['labels'][] = $month->format('M Y');
            $trend['values'][] = round($revenue, 2);
        }

        return $trend;
    }

    /**
     * Bookings created per month over the last six months, split into the
     * total requested and the subset that was approved.
     */
    private function bookingTrend(): array
    {
        $firstMonth = Carbon::now()->startOfMonth()->subMonths(5);

        $appointments = Appointment::where('created_at', '>=', $firstMonth)
            ->get(['id', 'status', 'created_at']);

        $trend = ['labels' => [], 'total' => [], 'approved' => []];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $inMonth = $appointments->filter(
                fn ($appt) => $appt->created_at->isSameMonth($month)
            );

            $trend['labels'][]   = $month->format('M Y');
            $trend['total'][]    = $inMonth->count();
            $trend['approved'][] = $inMonth->where('status', 'approved')->count();
        }

        return $trend;
    }

    /**
     * Booking volume per internet plan, keeping the six most requested plans
     * and folding the remainder into a single "Other plans" slice.
     */
    private function bookingsByPlan(int $limit = 6): array
    {
        $plans = Appointment::query()
            ->join('services', 'services.id', '=', 'appointments.service_id')
            ->selectRaw('services.service_name as label, COUNT(*) as bookings')
            ->groupBy('services.id', 'services.service_name')
            ->orderByDesc('bookings')
            ->orderBy('services.service_name')
            ->get();

        $top  = $plans->take($limit);
        $rest = $plans->slice($limit);

        $labels = $top->pluck('label')->map(
            fn ($label) => str_ireplace('CCTN', 'BCTVI', $label)
        )->values()->all();
        $values = $top->pluck('bookings')->map(fn ($count) => (int) $count)->values()->all();

        if ($rest->isNotEmpty()) {
            $labels[] = 'Other plans';
            $values[] = (int) $rest->sum('bookings');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function quickUpdateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status'         => 'required|in:approved,cancelled',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->update([
            'status'      => $request->status,
            'admin_notes' => $request->input('admin_notes', ''),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success_message', "Appointment #{$appointment->id} status set to {$request->status}.");
    }
}
