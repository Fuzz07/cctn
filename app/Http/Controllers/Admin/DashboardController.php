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
            'clients'          => Client::count(),
            'active_clients'   => Client::active()->count(),
            'inactive_clients' => Client::inactive()->count(),
            'total'            => Appointment::count(),
            'pending'          => Appointment::where('status', 'pending')->count(),
            'approved'         => Appointment::where('status', 'approved')->count(),
            'services'         => Service::where('status', 'Active')->count(),
        ];

        // Total revenue (all time)
        $stats['total_revenue'] = (float) Payment::sum('amount_paid');

        // Revenue this month
        $stats['revenue_this_month'] = (float) Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount_paid');

        // Revenue last month for comparison
        $lastMonth = now()->subMonth();
        $stats['revenue_last_month'] = (float) Payment::whereYear('payment_date', $lastMonth->year)
            ->whereMonth('payment_date', $lastMonth->month)
            ->sum('amount_paid');

        $bookingTrend      = $this->bookingTrend();
        $bookingsByPlan    = $this->bookingsByPlan();
        $salesRevenueTrend = $this->salesRevenueTrend();

        // Recent clients (last 5)
        $recentClients = Client::with('currentService')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Upcoming appointments (next 5, approved/pending, future dates)
        $upcomingAppointments = Appointment::with(['client', 'service'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('preferred_date', '>=', now()->toDateString())
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->limit(5)
            ->get();

        // Recent revenue payments (last 6)
        $recentPayments = Payment::with('client')
            ->orderByDesc('payment_date')
            ->limit(4)
            ->get();

        return view('admin.dashboard', compact(
            'admin',
            'stats',
            'bookingTrend',
            'bookingsByPlan',
            'salesRevenueTrend',
            'recentClients',
            'upcomingAppointments',
            'recentPayments'
        ));
    }

    /**
     * Collected sales revenue per month over the last six months.
     */
    private function salesRevenueTrend(): array
    {
        $firstMonth = Carbon::now()->startOfMonth()->subMonths(5);

        $payments = Payment::where('payment_date', '>=', $firstMonth)
            ->get(['amount_paid', 'payment_date']);

        $trend = ['labels' => [], 'values' => []];

        for ($i = 5; $i >= 0; $i--) {
            $month   = Carbon::now()->startOfMonth()->subMonths($i);
            $revenue = $payments
                ->filter(fn ($p) => $p->payment_date->isSameMonth($month))
                ->sum(fn ($p) => (float) $p->amount_paid);

            $trend['labels'][] = $month->format('M Y');
            $trend['values'][] = round($revenue, 2);
        }

        return $trend;
    }

    /**
     * Bookings created per month over the last six months.
     */
    private function bookingTrend(): array
    {
        $firstMonth = Carbon::now()->startOfMonth()->subMonths(5);

        $appointments = Appointment::where('created_at', '>=', $firstMonth)
            ->get(['id', 'status', 'created_at']);

        $trend = ['labels' => [], 'total' => [], 'approved' => []];

        for ($i = 5; $i >= 0; $i--) {
            $month   = Carbon::now()->startOfMonth()->subMonths($i);
            $inMonth = $appointments->filter(fn ($a) => $a->created_at->isSameMonth($month));

            $trend['labels'][]   = $month->format('M Y');
            $trend['total'][]    = $inMonth->count();
            $trend['approved'][] = $inMonth->where('status', 'approved')->count();
        }

        return $trend;
    }

    /**
     * Booking volume per internet plan (top 6 + Other).
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
        $values = $top->pluck('bookings')->map(fn ($c) => (int) $c)->values()->all();

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
