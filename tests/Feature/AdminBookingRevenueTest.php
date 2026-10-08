<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminBookingRevenueTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Client $client;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = Admin::create([
            'fullname' => 'Revenue Admin',
            'username' => 'revenue_admin',
            'password' => bcrypt('password'),
            'role'     => 'super_admin',
        ]);

        $this->client = Client::create([
            'account_number' => 'ACC-REVENUE-001',
            'firstname'      => 'Maria',
            'lastname'       => 'Santos',
            'email'          => 'maria@example.com',
            'username'       => 'mariasantos',
            'password'       => bcrypt('password'),
        ]);

        $this->service = Service::create([
            'service_name' => 'FTTH - 30 Mbps Plan',
            'description'  => 'Test internet plan',
            'price'        => 999,
            'status'       => 'Active',
        ]);
    }

    private function pendingBooking(): Appointment
    {
        return Appointment::create([
            'client_id'        => $this->client->id,
            'service_id'       => $this->service->id,
            'preferred_date'   => now()->addDays(2)->toDateString(),
            'preferred_time'   => '10:00:00',
            'status'           => 'pending',
            'payment_method'   => 'GCash',
            'reference_number' => '1234567890123',
            'payment_proof'    => 'payments/test-receipt.jpg',
        ]);
    }

    public function test_approving_a_booking_adds_it_to_sales_revenue_once(): void
    {
        $appointment = $this->pendingBooking();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.appointments.quick_update'), [
                'appointment_id' => $appointment->id,
                'status'         => 'approved',
            ])
            ->assertRedirect(route('admin.appointments'));

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointment->id,
            'client_id'      => $this->client->id,
            'amount_paid'    => 999,
            'payment_method' => 'GCash',
        ]);
        $this->assertDatabaseHas('billing_accounts', [
            'client_id'        => $this->client->id,
            'total_amount_due' => 999,
            'status'           => 'paid',
        ]);

        // Re-submitting approval must not count the same booking twice.
        $this->post(route('admin.appointments.quick_update'), [
            'appointment_id' => $appointment->id,
            'status'         => 'approved',
        ])->assertRedirect(route('admin.appointments'));

        $this->assertSame(1, Payment::where('appointment_id', $appointment->id)->count());
        $this->assertSame(999.0, (float) Payment::sum('amount_paid'));
    }

    public function test_dashboard_approval_path_also_adds_booking_revenue(): void
    {
        $appointment = $this->pendingBooking();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.dashboard.quick-update'), [
                'appointment_id' => $appointment->id,
                'status'         => 'approved',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointment->id,
            'amount_paid'    => 999,
        ]);
    }

    public function test_admin_can_view_booking_receipts_from_appointments_and_sales(): void
    {
        $appointment = $this->pendingBooking();
        $appointment->update(['status' => 'approved']);
        $payment = Payment::where('appointment_id', $appointment->id)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.appointments.receipt', $appointment))
            ->assertOk()
            ->assertSee($payment->receipt_no)
            ->assertSee('Approved &amp; Recorded in Sales', false);

        $this->get(route('admin.sales'))
            ->assertOk()
            ->assertSee($payment->receipt_no)
            ->assertSee(route('admin.sales.receipt', $payment), false);

        $this->get(route('admin.sales.receipt', $payment))
            ->assertOk()
            ->assertSee('Sales Revenue Receipt')
            ->assertSee('Booking #' . $appointment->id);
    }
}
