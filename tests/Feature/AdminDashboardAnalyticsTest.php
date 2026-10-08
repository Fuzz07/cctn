<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'fullname' => 'Test Administrator',
            'username' => 'admin_test',
            'password' => bcrypt('password123'),
            'role'     => 'super_admin',
        ]);

        $this->client = Client::create([
            'firstname' => 'Juan',
            'lastname'  => 'Dela Cruz',
            'email'     => 'juan@example.com',
            'username'  => 'juan',
            'password'  => Hash::make('password123'),
        ]);
    }

    private function makeService(string $name, float $price = 999): Service
    {
        return Service::create([
            'service_name'     => $name,
            'description'      => $name,
            'duration_minutes' => 60,
            'price'            => $price,
            'status'           => 'Active',
        ]);
    }

    private function makeAppointment(Service $service, string $status, Carbon $createdAt): Appointment
    {
        $appointment = Appointment::create([
            'client_id'      => $this->client->id,
            'service_id'     => $service->id,
            'preferred_date' => $createdAt->toDateString(),
            'preferred_time' => '08:00:00',
            'status'         => $status,
        ]);

        // created_at drives the trend buckets, so pin it explicitly.
        $appointment->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $appointment;
    }

    public function test_dashboard_exposes_six_month_booking_trend()
    {
        $service = $this->makeService('FTTH - 10 Mbps');

        $thisMonth = Carbon::now()->startOfMonth()->addDays(2);
        $lastMonth = Carbon::now()->startOfMonth()->subMonth()->addDays(3);
        $tooOld    = Carbon::now()->startOfMonth()->subMonths(9);

        $this->makeAppointment($service, 'approved', $thisMonth);
        $this->makeAppointment($service, 'pending', $thisMonth);
        $this->makeAppointment($service, 'approved', $lastMonth);
        $this->makeAppointment($service, 'cancelled', $tooOld);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $trend = $response->viewData('bookingTrend');

        $this->assertCount(6, $trend['labels']);
        $this->assertCount(6, $trend['total']);
        $this->assertCount(6, $trend['approved']);

        // Oldest bucket first, current month last.
        $this->assertSame(Carbon::now()->format('M Y'), end($trend['labels']));
        $this->assertSame(2, $trend['total'][5]);
        $this->assertSame(1, $trend['approved'][5]);
        $this->assertSame(1, $trend['total'][4]);
        $this->assertSame(1, $trend['approved'][4]);

        // The 9-month-old booking falls outside the window entirely.
        $this->assertSame(3, array_sum($trend['total']));
    }

    public function test_dashboard_groups_bookings_by_plan_and_folds_the_tail()
    {
        $now = Carbon::now()->startOfMonth()->addDay();

        $popular = $this->makeService('FTTH - 10 Mbps');
        foreach (range(1, 3) as $i) {
            $this->makeAppointment($popular, 'approved', $now);
        }

        // Seven more plans, one booking each, so the eighth onwards folds into "Other plans".
        foreach (range(1, 7) as $i) {
            $this->makeAppointment($this->makeService("Plan {$i}"), 'pending', $now);
        }

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $plans = $response->viewData('bookingsByPlan');

        // Six named plans + the folded "Other plans" bar.
        $this->assertCount(7, $plans['labels']);
        $this->assertCount(7, $plans['values']);
        $this->assertSame('FTTH - 10 Mbps', $plans['labels'][0]);
        $this->assertSame(3, $plans['values'][0]);
        $this->assertSame('Other plans', end($plans['labels']));
        $this->assertSame(2, end($plans['values']));
        $this->assertSame(10, array_sum($plans['values']));
    }

    public function test_dashboard_renders_both_charts()
    {
        $service = $this->makeService('FTTH - 10 Mbps');
        $this->makeAppointment($service, 'approved', Carbon::now());

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Booking Trend');
        $response->assertSee('Bookings by Plan');
        $response->assertSee('id="bookingTrendChart"', false);
        $response->assertSee('id="bookingsByPlanChart"', false);
        // Each chart ships a table twin so no value is hover-only.
        $response->assertSee('id="booking-trend-table"', false);
        $response->assertSee('id="bookings-plan-table"', false);
    }

    public function test_dashboard_renders_without_any_bookings()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('No bookings recorded in the last 6 months yet.');
        $response->assertSee('No plan bookings to chart yet.');
        $this->assertSame([], $response->viewData('bookingsByPlan')['labels']);
    }
}
