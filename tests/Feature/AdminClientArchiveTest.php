<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClientArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        config(['services.recaptcha.enabled' => false]);

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

    public function test_guest_cannot_archive_a_client()
    {
        $this->post(route('admin.clients.archive', $this->client->id))
            ->assertRedirect(route('admin.login'));

        $this->assertNull($this->client->fresh()->archived_at);
    }

    public function test_admin_can_archive_a_client()
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.archive', $this->client->id))
            ->assertRedirect()
            ->assertSessionHas('success_message');

        $this->assertNotNull($this->client->fresh()->archived_at);
    }

    public function test_deactivating_a_subscription_keeps_the_client_signed_in_on_mobile()
    {
        $this->client->createToken('mobile-app');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.archive', $this->client->id));

        // Inactive clients keep app access so they can subscribe again.
        $this->assertSame(1, $this->client->tokens()->count());
    }

    public function test_unsubscribe_endpoint_sets_the_subscription_inactive()
    {
        $this->client->update([
            'account_status' => 'Active',
            'subscription_status' => 'active',
        ]);
        $this->client->createToken('mobile-app');

        $this->actingAs($this->client, 'client')
            ->post(route('client.unsubscribe'))
            ->assertRedirect(route('client.settings', ['tab' => 'service']))
            ->assertSessionHas('success_message');

        $client = $this->client->fresh();
        $this->assertSame('Inactive', $client->account_status);
        $this->assertSame('cancelled', $client->subscription_status);
        $this->assertSame(1, $client->tokens()->count());
        $this->assertAuthenticatedAs($client, 'client');
    }

    public function test_inactive_clients_are_hidden_from_the_default_list_but_shown_under_inactive()
    {
        $this->client->update(['archived_at' => now()]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients'))
            ->assertOk()
            ->assertDontSee('Dela Cruz');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'inactive']))
            ->assertOk()
            ->assertSee('Dela Cruz')
            ->assertSee('Inactive');
    }

    public function test_admin_can_restore_an_archived_client()
    {
        $this->client->update(['archived_at' => now()]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.restore', $this->client->id))
            ->assertRedirect()
            ->assertSessionHas('success_message');

        $this->assertNull($this->client->fresh()->archived_at);
    }

    public function test_inactive_client_can_sign_in_on_the_website_to_subscribe_again()
    {
        $this->client->deactivateSubscription('cancelled');

        $this->post('/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
            'agree_terms' => '1',
        ])->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($this->client->fresh(), 'client');
    }

    public function test_inactive_client_can_sign_in_on_the_mobile_api()
    {
        $this->client->deactivateSubscription('cancelled');

        $this->postJson('/api/v1/auth/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['token'])
            ->assertJsonPath('client.account_status', 'Inactive');
    }

    public function test_restored_client_can_sign_in_again()
    {
        $this->client->update(['archived_at' => now()]);
        $this->client->update(['archived_at' => null]);

        $this->postJson('/api/v1/auth/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_signed_in_client_stays_signed_in_once_deactivated()
    {
        $this->actingAs($this->client, 'client')
            ->get(route('client.dashboard'))
            ->assertOk();

        $this->client->deactivateSubscription('cancelled');

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Inactive');

        $this->assertAuthenticatedAs($this->client->fresh(), 'client');
    }
}
