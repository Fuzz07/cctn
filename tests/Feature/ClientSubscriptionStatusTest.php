<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSubscriptionStatusTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'firstname' => 'Subscription',
            'lastname' => 'Client',
            'email' => 'subscription@example.com',
            'username' => 'subscription-client',
            'password' => bcrypt('password123'),
        ]);

        $this->service = Service::create([
            'service_name' => 'Fiber 100',
            'price' => 1499,
            'status' => 'Active',
        ]);
    }

    public function test_approving_a_plan_automatically_activates_the_client(): void
    {
        $appointment = $this->createAppointment('approved');
        $client = $this->client->fresh();

        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertSame($this->service->id, $client->current_service_id);
        $this->assertSame($appointment->id, $client->current_appointment_id);
        $this->assertNull($client->archived_at);
    }

    public function test_cancelling_the_current_plan_automatically_inactivates_the_client(): void
    {
        $appointment = $this->createAppointment('approved');
        $appointment->update(['status' => 'cancelled']);

        $client = $this->client->fresh();
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('cancelled', $client->subscription_status);
        $this->assertNotNull($client->subscription_cancelled_at);
        $this->assertNotNull($client->archived_at);
    }

    public function test_expired_plan_is_automatically_marked_inactive(): void
    {
        $this->createAppointment('approved', now()->subDay());

        $this->artisan('subscriptions:expire')
            ->expectsOutput('Expired 1 client subscription(s).')
            ->assertSuccessful();

        $client = $this->client->fresh();
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('expired', $client->subscription_status);
        $this->assertNotNull($client->archived_at);
    }

    public function test_new_approved_plan_reactivates_a_cancelled_subscription(): void
    {
        $oldAppointment = $this->createAppointment('approved');
        $oldAppointment->update(['status' => 'cancelled']);

        $newService = Service::create([
            'service_name' => 'Fiber 150',
            'price' => 1699,
            'status' => 'Active',
        ]);

        $newAppointment = $this->createAppointment('approved', null, $newService);
        $client = $this->client->fresh();

        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertSame($newService->id, $client->current_service_id);
        $this->assertSame($newAppointment->id, $client->current_appointment_id);
        $this->assertNull($client->archived_at);
    }

    public function test_admin_inactive_list_shows_cancelled_subscription_and_plan(): void
    {
        $appointment = $this->createAppointment('approved');
        $appointment->update(['status' => 'cancelled']);

        $admin = Admin::create([
            'fullname' => 'Test Administrator',
            'username' => 'subscription_admin',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'inactive']))
            ->assertOk()
            ->assertSee('Inactive')
            ->assertSee('Subscription cancelled')
            ->assertSee('Fiber 100');
    }

    private function createAppointment(
        string $status,
        $subscriptionEndsAt = null,
        ?Service $service = null
    ): Appointment {
        return Appointment::create([
            'client_id' => $this->client->id,
            'service_id' => ($service ?? $this->service)->id,
            'preferred_date' => now()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00:00',
            'status' => $status,
            'subscription_ends_at' => $subscriptionEndsAt,
        ]);
    }
}
