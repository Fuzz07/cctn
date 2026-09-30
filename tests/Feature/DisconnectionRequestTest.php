<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DisconnectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Client $client;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'fullname' => 'Review Administrator',
            'username' => 'review_admin',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $this->client = Client::create([
            'firstname' => 'Active',
            'lastname' => 'Subscriber',
            'email' => 'active@example.com',
            'username' => 'active-subscriber',
            'password' => bcrypt('password123'),
        ]);

        $this->service = Service::create([
            'service_name' => 'Fiber 100',
            'price' => 1499,
            'status' => 'Active',
        ]);

        Appointment::create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'preferred_date' => now()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00:00',
            'status' => 'approved',
        ]);

        $this->client->refresh();
    }

    public function test_client_request_remains_active_until_an_admin_approves_it(): void
    {
        $this->client->createToken('mobile-app');

        $this->actingAs($this->client, 'client')
            ->post(route('client.disconnection.request'))
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('pending', $client->disconnection_request_status);
        $this->assertNotNull($client->disconnection_requested_at);
        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertNull($client->archived_at);
        $this->assertSame(1, $client->tokens()->count());
        $this->assertAuthenticatedAs($client, 'client');

        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Disconnection Request',
        ]);
    }

    public function test_duplicate_request_does_not_create_another_notification(): void
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.disconnection.request'))
            ->assertRedirect(route('client.dashboard'));

        $this->post(route('client.disconnection.request'))
            ->assertRedirect(route('client.dashboard'));

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_mobile_client_can_submit_a_disconnection_request(): void
    {
        Sanctum::actingAs($this->client);

        $this->postJson('/api/v1/profile/request-disconnection')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('client.account_status', 'Active')
            ->assertJsonPath('client.subscription_status', 'active')
            ->assertJsonPath('client.disconnection_request_status', 'pending');

        $client = $this->client->fresh();
        $this->assertTrue($client->isAccountActive());
        $this->assertTrue($client->hasPendingDisconnectionRequest());
        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Disconnection Request',
        ]);

        $this->postJson('/api/v1/profile/request-disconnection')
            ->assertOk()
            ->assertJsonPath('client.disconnection_request_status', 'pending');

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_client_without_a_service_cannot_request_disconnection(): void
    {
        $client = Client::create([
            'firstname' => 'Never',
            'lastname' => 'Subscribed',
            'email' => 'never@example.com',
            'username' => 'never-subscribed',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($client);

        $this->postJson('/api/v1/profile/request-disconnection')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You do not have a connected service to disconnect.');

        $this->assertNull($client->fresh()->disconnection_request_status);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_inactive_client_can_still_request_disconnection_separately(): void
    {
        $this->client->unsubscribe();

        $this->actingAs($this->client->fresh(), 'client')
            ->post(route('client.disconnection.request'))
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('pending', $client->disconnection_request_status);
        // The request leaves the subscription status exactly as it was.
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('cancelled', $client->subscription_status);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'disconnection_requests']))
            ->assertOk()
            ->assertSee('Active Subscriber')
            ->assertSee('Approve Disconnection');
    }

    public function test_admin_can_approve_a_pending_disconnection_request(): void
    {
        $this->client->requestDisconnection();
        $this->client->createToken('mobile-app');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.disconnection.approve', $this->client->id))
            ->assertRedirect(route('admin.clients', ['filter' => 'inactive']))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('approved', $client->disconnection_request_status);
        $this->assertNotNull($client->disconnection_reviewed_at);
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('cancelled', $client->subscription_status);

        // Disconnecting keeps the account, its subscription history and its sign-in.
        $this->assertSame($this->service->id, $client->current_service_id);
        $this->assertSame(1, $client->appointments()->count());
        $this->assertSame(1, $client->tokens()->count());

        $this->assertDatabaseHas('notifications', [
            'for_admin' => false,
            'client_id' => $client->id,
            'title' => 'Disconnection Approved',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'inactive']))
            ->assertOk()
            ->assertSee('Active Subscriber')
            ->assertSee('Inactive')
            ->assertSee('Subscription cancelled');
    }

    public function test_admin_can_reject_a_request_without_deactivating_the_subscription(): void
    {
        $this->client->requestDisconnection();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.disconnection.reject', $this->client->id))
            ->assertRedirect(route('admin.clients', ['filter' => 'disconnection_requests']))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('rejected', $client->disconnection_request_status);
        $this->assertNotNull($client->disconnection_reviewed_at);
        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertNull($client->archived_at);

        $this->assertDatabaseHas('notifications', [
            'for_admin' => false,
            'client_id' => $client->id,
            'title' => 'Disconnection Request Declined',
        ]);
    }

    public function test_admin_request_filter_shows_pending_subscribers(): void
    {
        $this->client->requestDisconnection();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'disconnection_requests']))
            ->assertOk()
            ->assertSee('Active Subscriber')
            ->assertSee('Disconnection requested')
            ->assertSee('Approve Disconnection');
    }

    public function test_account_without_an_active_subscription_is_listed_as_inactive(): void
    {
        $client = Client::create([
            'firstname' => 'No Plan',
            'lastname' => 'Client',
            'email' => 'no-plan@example.com',
            'username' => 'no-plan-client',
            'password' => bcrypt('password123'),
            'account_status' => 'Active',
            'subscription_status' => null,
        ]);

        $this->assertFalse($client->isAccountActive());

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'inactive']))
            ->assertOk()
            ->assertSee('No Plan Client')
            ->assertSee('No active subscription');
    }

    public function test_disconnected_client_keeps_portal_access_and_cannot_request_again(): void
    {
        $this->client->requestDisconnection();
        $this->client->approveDisconnection();

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Disconnected')
            ->assertSee('Subscribe to a plan to reconnect.')
            ->assertSee('Fiber 100');

        $this->post(route('client.disconnection.request'))
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('error_message', 'Your service has already been disconnected.');

        $this->assertSame('approved', $this->client->fresh()->disconnection_request_status);
    }

    public function test_approving_disconnection_keeps_the_reason_an_ended_subscription_ended_with(): void
    {
        $this->client->deactivateSubscription('expired');
        $this->client->requestDisconnection();

        $this->assertTrue($this->client->approveDisconnection());

        $client = $this->client->fresh();
        $this->assertSame('approved', $client->disconnection_request_status);
        $this->assertSame('expired', $client->subscription_status);
    }

    public function test_subscribing_again_after_disconnection_reconnects_the_service(): void
    {
        $this->client->requestDisconnection();
        $this->client->approveDisconnection();

        Appointment::create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'status' => 'approved',
        ]);

        $client = $this->client->fresh();
        $this->assertSame('Active', $client->account_status);
        $this->assertSame('active', $client->subscription_status);
        $this->assertNull($client->disconnection_request_status);
        $this->assertTrue($client->canRequestDisconnection());
        $this->assertSame(2, $client->appointments()->count());
    }
}
