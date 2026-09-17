<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BillingAccount;
use App\Models\Client;
use App\Models\Service;
use App\Models\Appointment;
use App\Notifications\AppointmentPaymentConfirmedNotification;
use App\Notifications\BillingPaymentReceiptNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function admin(): Admin
    {
        return Admin::create([
            'fullname' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
        ]);
    }

    private function client(array $overrides = []): Client
    {
        return Client::create(array_merge([
            'firstname' => 'Jane',
            'lastname'  => 'Doe',
            'email'     => 'jane.doe@example.com',
            'username'  => 'janedoe',
            'password'  => bcrypt('password'),
        ], $overrides));
    }

    private function service(): Service
    {
        return Service::create([
            'service_name' => 'FTTH - 25 Mbps Plan',
            'description'  => 'Test plan',
            'price'        => 999.00,
            'status'       => 'Active',
        ]);
    }

    private function billing(Client $client): BillingAccount
    {
        if (!$client->account_number) {
            $client->update(['account_number' => 'ACCT-0001']);
        }
        return BillingAccount::create([
            'client_id'        => $client->id,
            'account_number'   => $client->account_number,
            'statement_period' => 'September 2026',
            'amount_due'       => 999.00,
            'penalty_amount'   => 0,
            'total_amount_due' => 999.00,
            'due_date'         => now()->addDays(7),
            'status'           => 'unpaid',
        ]);
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_recording_billing_payment_sends_receipt_email_to_client(): void
    {
        Notification::fake();

        $admin   = $this->admin();
        $client  = $this->client();
        $billing = $this->billing($client);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.billing.payment'), [
                'billing_id'       => $billing->id,
                'amount_paid'      => 999.00,
                'payment_method'   => 'gcash',
                'reference_number' => 'GCX-123456',
                'received_by'      => 'Test Admin',
            ])
            ->assertRedirect(route('admin.billing'));

        Notification::assertSentTo(
            $client,
            BillingPaymentReceiptNotification::class,
            function (BillingPaymentReceiptNotification $notification) use ($billing) {
                return $notification->billing->id === $billing->id
                    && $notification->payment->amount_paid == 999.00;
            }
        );
    }

    public function test_approving_appointment_with_payment_sends_confirmation_email(): void
    {
        Notification::fake();

        $admin   = $this->admin();
        $client  = $this->client();
        $service = $this->service();

        $appointment = Appointment::create([
            'client_id'        => $client->id,
            'service_id'       => $service->id,
            'preferred_date'   => now()->addDays(3)->format('Y-m-d'),
            'preferred_time'   => '10:00:00',
            'status'           => 'pending',
            'payment_method'   => 'GCash',
            'reference_number' => 'GC-TEST-9999',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.appointments.quick_update'), [
                'appointment_id' => $appointment->id,
                'status'         => 'approved',
            ])
            ->assertRedirect(route('admin.appointments'));

        Notification::assertSentTo(
            $client,
            AppointmentPaymentConfirmedNotification::class,
            function (AppointmentPaymentConfirmedNotification $notification) use ($appointment) {
                return $notification->appointment->id === $appointment->id;
            }
        );
    }

    public function test_no_email_sent_when_client_has_no_email(): void
    {
        Notification::fake();

        $admin   = $this->admin();
        // Use a unique username to avoid conflicts; email is empty string (NOT NULL column)
        $client  = $this->client(['email' => '', 'username' => 'noemailuser']);
        $billing = $this->billing($client);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.billing.payment'), [
                'billing_id'  => $billing->id,
                'amount_paid' => 500.00,
            ])
            ->assertRedirect(route('admin.billing'));

        Notification::assertNothingSentTo($client);
    }

    public function test_payment_confirmation_email_not_sent_when_cancelling(): void
    {
        Notification::fake();

        $admin   = $this->admin();
        $client  = $this->client();
        $service = $this->service();

        $appointment = Appointment::create([
            'client_id'      => $client->id,
            'service_id'     => $service->id,
            'preferred_date' => now()->addDays(3)->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'status'         => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.appointments.quick_update'), [
                'appointment_id' => $appointment->id,
                'status'         => 'cancelled',
            ])
            ->assertRedirect(route('admin.appointments'));

        // Cancelled should not send a payment confirmation
        Notification::assertNotSentTo($client, AppointmentPaymentConfirmedNotification::class);
    }

    public function test_admin_can_view_email_diagnostics(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.mail.test'));

        $response->assertStatus(200);
        $response->assertSee('Email Diagnostics');
        $response->assertSee('Current Active Mail Settings');
    }

    public function test_admin_can_send_test_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.mail.test.send'), [
                'test_email' => 'recipient@example.com',
            ]);

        $response->assertSessionHas('success_message');
    }

    public function test_artisan_mail_test_command(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->artisan('mail:test', ['email' => 'test@example.com'])
            ->assertExitCode(0);
    }

    public function test_storage_receipt_file_can_be_viewed(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('payments/receipt123.jpg', 'fake-image-content');

        $response = $this->get('/storage/payments/receipt123.jpg');
        $response->assertStatus(200);
    }
}
