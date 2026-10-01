<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_client_can_unsubscribe_and_keeps_account_and_history(): void
    {
        $this->createAppointment('approved');
        $this->client->refresh()->createToken('mobile-app');

        $this->actingAs($this->client, 'client')
            ->post(route('client.unsubscribe'))
            ->assertRedirect(route('client.settings', ['tab' => 'service']))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('cancelled', $client->subscription_status);
        $this->assertNotNull($client->subscription_cancelled_at);

        // The account, its plan history and its sign-in all remain.
        $this->assertSame($this->service->id, $client->current_service_id);
        $this->assertSame(1, $client->appointments()->count());
        $this->assertSame(1, $client->tokens()->count());
        $this->assertAuthenticatedAs($client, 'client');

        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Client Unsubscribed',
        ]);
    }

    public function test_unsubscribing_is_separate_from_disconnection(): void
    {
        $this->createAppointment('approved');
        $this->client->refresh()->requestDisconnection();

        $this->actingAs($this->client->fresh(), 'client')
            ->post(route('client.unsubscribe'))
            ->assertRedirect(route('client.settings', ['tab' => 'service']));

        $client = $this->client->fresh();
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('pending', $client->disconnection_request_status);
        $this->assertNull($client->disconnection_reviewed_at);
    }

    public function test_client_without_an_active_subscription_cannot_unsubscribe(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.unsubscribe'))
            ->assertRedirect(route('client.settings', ['tab' => 'service']))
            ->assertSessionHas('error_message', 'You do not have an active subscription to cancel.');

        $this->assertNull($this->client->fresh()->subscription_status);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_mobile_client_can_unsubscribe(): void
    {
        $this->createAppointment('approved');
        Sanctum::actingAs($this->client->fresh());

        $this->postJson('/api/v1/profile/unsubscribe')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('client.account_status', 'Inactive')
            ->assertJsonPath('client.subscription_status', 'cancelled')
            ->assertJsonPath('client.current_plan', 'Fiber 100')
            ->assertJsonPath('client.can_request_disconnection', true);

        $this->postJson('/api/v1/profile/unsubscribe')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'You do not have an active subscription to cancel.');
    }

    public function test_active_settings_offers_unsubscribe_and_a_separate_disconnection_option(): void
    {
        $this->createAppointment('approved');

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.settings', ['tab' => 'service']))
            ->assertOk()
            ->assertSee('Subscription')
            ->assertSee('Active')
            ->assertSee(route('client.unsubscribe'))
            ->assertSee('Disconnection')
            ->assertSee(route('client.disconnection.request'));
    }

    public function test_inactive_settings_lists_plans_to_subscribe_again(): void
    {
        $this->createAppointment('approved')->update(['status' => 'cancelled']);
        $repairVisit = Service::create([
            'service_name' => 'Technical Repair Visit',
            'price' => 0,
            'status' => 'Active',
        ]);

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.settings', ['tab' => 'service']))
            ->assertOk()
            ->assertSee('Inactive')
            ->assertSee('Subscription cancelled')
            ->assertSee('Fiber 100')
            ->assertSee(route('client.book', ['service_id' => $this->service->id]), false)
            ->assertDontSee(route('client.book', ['service_id' => $repairVisit->id]), false)
            ->assertDontSee(route('client.unsubscribe'));
    }

    public function test_inactive_client_subscribes_again_and_becomes_active_once_activated(): void
    {
        $this->createAppointment('approved');
        $this->actingAs($this->client->fresh(), 'client')->post(route('client.unsubscribe'));
        $this->assertSame('Inactive', $this->client->fresh()->account_status);

        $newService = Service::create([
            'service_name' => 'Fiber 150',
            'price' => 1699,
            'status' => 'Active',
        ]);
        $request = $this->createAppointment('pending', null, $newService);

        $this->get(route('client.settings', ['tab' => 'service']))
            ->assertOk()
            ->assertSee('Fiber 150 is awaiting activation');
        $this->assertSame('Inactive', $this->client->fresh()->account_status);

        $request->update(['status' => 'approved']);

        $client = $this->client->fresh();
        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertSame($newService->id, $client->current_service_id);
        $this->assertSame(2, $client->appointments()->count());
    }

    public function test_subscription_that_expires_mid_session_keeps_the_client_signed_in(): void
    {
        $this->createAppointment('approved', now()->subMinute());

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Subscription expired');

        $this->assertSame('expired', $this->client->fresh()->subscription_status);
        $this->assertAuthenticated('client');
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
