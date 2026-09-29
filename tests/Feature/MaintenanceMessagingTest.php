<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceMessagingTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Admin $admin;
    private MaintenanceRequest $maintenance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'firstname' => 'Support',
            'lastname' => 'Client',
            'email' => 'support-client@example.com',
            'username' => 'support-client',
            'password' => bcrypt('password123'),
        ]);

        $this->admin = Admin::create([
            'fullname' => 'Support Administrator',
            'username' => 'support-admin',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $this->maintenance = MaintenanceRequest::create([
            'client_id' => $this->client->id,
            'subject' => 'Intermittent connection',
            'description' => 'The connection drops every few minutes.',
            'priority' => 'High',
            'status' => 'Open',
        ]);
    }

    public function test_client_can_add_a_message_to_their_request(): void
    {
        Sanctum::actingAs($this->client);

        $this->postJson("/api/v1/maintenance/{$this->maintenance->id}/messages", [
            'message' => 'The modem LOS light is now blinking red.',
        ])->assertCreated()
            ->assertJsonPath('maintenance_message.sender_type', 'client')
            ->assertJsonPath('maintenance_message.message', 'The modem LOS light is now blinking red.');

        $this->assertDatabaseHas('maintenance_messages', [
            'maintenance_request_id' => $this->maintenance->id,
            'sender_type' => 'client',
            'sender_id' => $this->client->id,
            'message' => 'The modem LOS light is now blinking red.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $this->client->id,
            'title' => 'Maintenance Message',
        ]);
    }

    public function test_client_cannot_message_another_clients_request(): void
    {
        $otherClient = Client::create([
            'firstname' => 'Other',
            'lastname' => 'Client',
            'email' => 'other-support@example.com',
            'username' => 'other-support',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($otherClient);

        $this->postJson("/api/v1/maintenance/{$this->maintenance->id}/messages", [
            'message' => 'This should not be accepted.',
        ])->assertNotFound();

        $this->assertDatabaseCount('maintenance_messages', 0);
    }

    public function test_closed_request_rejects_new_client_messages(): void
    {
        $this->maintenance->update(['status' => 'Closed']);
        Sanctum::actingAs($this->client);

        $this->postJson("/api/v1/maintenance/{$this->maintenance->id}/messages", [
            'message' => 'Can this be reopened?',
        ])->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('maintenance_messages', 0);
    }

    public function test_admin_can_reply_and_manage_the_request_status(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.maintenance.update'), [
                'request_id' => $this->maintenance->id,
                'status' => 'In Progress',
                'reply_message' => 'A technician has been assigned and will contact you today.',
            ])
            ->assertRedirect(route('admin.maintenance'))
            ->assertSessionHas('success_message');

        $maintenance = $this->maintenance->fresh();
        $this->assertSame('In Progress', $maintenance->status);
        $this->assertSame(
            'A technician has been assigned and will contact you today.',
            $maintenance->follow_up_note,
        );
        $this->assertDatabaseHas('maintenance_messages', [
            'maintenance_request_id' => $maintenance->id,
            'sender_type' => 'admin',
            'sender_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'for_admin' => false,
            'client_id' => $this->client->id,
            'title' => 'Maintenance Request Update',
        ]);
    }

    public function test_client_request_list_contains_the_message_conversation(): void
    {
        $this->maintenance->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $this->admin->id,
            'message' => 'Please restart the modem once.',
        ]);
        Sanctum::actingAs($this->client);

        $this->getJson('/api/v1/maintenance')
            ->assertOk()
            ->assertJsonPath('requests.0.messages.0.sender_type', 'admin')
            ->assertJsonPath('requests.0.messages.0.message', 'Please restart the modem once.');
    }

    public function test_admin_page_displays_client_messages_and_reply_controls(): void
    {
        $this->maintenance->messages()->create([
            'sender_type' => 'client',
            'sender_id' => $this->client->id,
            'message' => 'The connection is still dropping tonight.',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.maintenance'))
            ->assertOk()
            ->assertSee('The connection is still dropping tonight.')
            ->assertSee('Reply to the client')
            ->assertSee('Save &amp; Send Reply', false);
    }

    public function test_new_mobile_request_uses_database_compatible_status_values(): void
    {
        Sanctum::actingAs($this->client);

        $this->postJson('/api/v1/maintenance', [
            'subject' => 'No connection',
            'description' => 'The modem has no internet connection.',
            'priority' => 'medium',
        ])->assertCreated()
            ->assertJsonPath('request.status', 'Open')
            ->assertJsonPath('request.priority', 'Medium');

        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $this->client->id,
            'title' => 'New Maintenance Request',
        ]);
    }
}
