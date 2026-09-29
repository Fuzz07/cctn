<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAppointmentDeletionTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'firstname' => 'Delete',
            'lastname' => 'Tester',
            'email' => 'delete@example.com',
            'username' => 'delete-tester',
            'password' => bcrypt('password123'),
        ]);

        $this->service = Service::create([
            'service_name' => 'Delete Test Plan',
            'price' => 999,
            'status' => 'Active',
        ]);
    }

    public function test_client_can_delete_their_pending_booking(): void
    {
        $appointment = $this->appointmentFor($this->client, 'pending');

        $this->actingAs($this->client, 'client')
            ->delete(route('client.appointments.destroy', $appointment->id))
            ->assertRedirect(route('client.appointments'))
            ->assertSessionHas('success_message');

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
        $this->assertDatabaseHas('notifications', [
            'for_admin' => true,
            'client_id' => $this->client->id,
            'title' => 'Booking Deleted',
        ]);
    }

    public function test_client_cannot_delete_another_clients_booking(): void
    {
        $otherClient = Client::create([
            'firstname' => 'Other',
            'lastname' => 'Client',
            'email' => 'other-delete@example.com',
            'username' => 'other-delete-client',
            'password' => bcrypt('password123'),
        ]);
        $appointment = $this->appointmentFor($otherClient, 'pending');

        $this->actingAs($this->client, 'client')
            ->delete(route('client.appointments.destroy', $appointment->id))
            ->assertNotFound();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);
    }

    public function test_client_cannot_delete_an_approved_booking(): void
    {
        $appointment = $this->appointmentFor($this->client, 'approved');

        $this->actingAs($this->client, 'client')
            ->delete(route('client.appointments.destroy', $appointment->id))
            ->assertRedirect(route('client.appointments'))
            ->assertSessionHasErrors('cancel');

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);
    }

    private function appointmentFor(Client $client, string $status): Appointment
    {
        return Appointment::create([
            'client_id' => $client->id,
            'service_id' => $this->service->id,
            'preferred_date' => now()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00:00',
            'status' => $status,
        ]);
    }
}
