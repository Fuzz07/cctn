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

    public function test_archiving_revokes_mobile_app_tokens()
    {
        $this->client->createToken('mobile-app');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.archive', $this->client->id));

        $this->assertSame(0, $this->client->tokens()->count());
    }

    public function test_archived_clients_are_hidden_from_the_default_list_but_shown_under_archived()
    {
        $this->client->update(['archived_at' => now()]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients'))
            ->assertOk()
            ->assertDontSee('Dela Cruz');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients', ['filter' => 'archived']))
            ->assertOk()
            ->assertSee('Dela Cruz')
            ->assertSee('Re-subscribe');
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

    public function test_archived_client_cannot_sign_in_on_the_website()
    {
        $this->client->update(['archived_at' => now()]);

        $this->post('/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
            'agree_terms' => '1',
        ])->assertSessionHasErrors('login_input');

        $this->assertGuest('client');
    }

    public function test_archived_client_cannot_sign_in_on_the_mobile_api()
    {
        $this->client->update(['archived_at' => now()]);

        $this->postJson('/api/v1/auth/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors(['login_input']);
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

    public function test_signed_in_client_is_logged_out_once_archived()
    {
        $this->actingAs($this->client, 'client')
            ->get(route('client.dashboard'))
            ->assertOk();

        $this->client->update(['archived_at' => now()]);

        $this->actingAs($this->client->fresh(), 'client')
            ->get(route('client.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest('client');
    }
}
