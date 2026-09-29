<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProofOfBillingTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'fullname' => 'Proof Reviewer',
            'username' => 'proof-reviewer',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $this->client = Client::create([
            'firstname' => 'Proof',
            'lastname' => 'Client',
            'email' => 'proof-client@example.com',
            'username' => 'proof-client',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_admin_can_view_a_public_upload_inline(): void
    {
        $relativePath = 'uploads/proof_of_billing/test-proof-' . uniqid() . '.png';
        $absolutePath = public_path($relativePath);
        File::ensureDirectoryExists(dirname($absolutePath));
        File::put($absolutePath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
        $this->client->update(['proof_of_billing' => $relativePath]);

        try {
            $this->actingAs($this->admin, 'admin')
                ->get(route('admin.clients.proof-of-billing', $this->client->id))
                ->assertOk()
                ->assertHeader('content-disposition', 'inline; filename="' . basename($absolutePath) . '"')
                ->assertHeader('x-content-type-options', 'nosniff');
        } finally {
            File::delete($absolutePath);
        }
    }

    public function test_guest_cannot_view_a_clients_proof_of_billing(): void
    {
        $this->client->update(['proof_of_billing' => 'uploads/proof_of_billing/private.png']);

        $this->get(route('admin.clients.proof-of-billing', $this->client->id))
            ->assertRedirect(route('admin.login'));
    }

    public function test_missing_proof_file_returns_a_clear_admin_error(): void
    {
        $this->client->update(['proof_of_billing' => 'uploads/proof_of_billing/missing.png']);

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.clients'))
            ->get(route('admin.clients.proof-of-billing', $this->client->id))
            ->assertRedirect(route('admin.clients'))
            ->assertSessionHas('error_message');
    }

    public function test_admin_can_replace_a_missing_proof_and_view_the_new_image(): void
    {
        Storage::fake('public');
        $this->client->update(['proof_of_billing' => 'uploads/proof_of_billing/missing.png']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.proof-of-billing.update', $this->client->id), [
                'proof_of_billing' => UploadedFile::fake()->image('replacement.jpg', 600, 800),
            ])
            ->assertRedirect()
            ->assertSessionHas('success_message');

        $storedValue = $this->client->fresh()->proof_of_billing;
        $this->assertStringStartsWith('storage/proof_of_billing/', $storedValue);
        Storage::disk('public')->assertExists(substr($storedValue, strlen('storage/')));

        $this->get(route('admin.clients.proof-of-billing', $this->client->id))
            ->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff');
    }
}
