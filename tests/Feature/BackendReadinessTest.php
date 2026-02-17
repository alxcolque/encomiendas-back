<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Office;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class BackendReadinessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Authentication (Login with Phone and PIN)
     */
    public function test_auth_login_with_phone_and_pin()
    {
        $user = User::factory()->create([
            'phone' => '12345678',
            'pin' => '1234', // Model cast handles hashing
            'role' => 'admin'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '12345678',
            'pin' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'accessToken']);
    }

    public function test_auth_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'phone' => '12345678',
            'pin' => '1234',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '12345678',
            'pin' => '0000', // Wrong PIN
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test User Management 
     */
    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/users', [
            'name' => 'New Driver',
            'email' => 'driver@example.com',
            'phone' => '87654321',
            'pin' => '5678', // 4 digits
            'role' => 'driver',
            'password' => 'password', // Still required by DB/Factory if not nullable, but ignored by auth
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['phone' => '87654321']);
    }

    /**
     * Test Office Management
     */
    public function test_can_list_offices()
    {
        Office::factory()->count(3)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/offices');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test Public Tracking
     */
    public function test_public_tracking_endpoint()
    {
        // Create necessary offices first
        $origin = Office::factory()->create();
        $dest = Office::factory()->create();

        $shipment = Shipment::create([
            'tracking_code' => 'TRACK123',
            'origin_office_id' => $origin->id,
            'destination_office_id' => $dest->id,
            'sender_name' => 'Sender',
            'sender_phone' => '111',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '222',
            'current_status' => 'created',
            'price' => 100,
            'estimated_delivery' => now()->addDays(2), // Required field
        ]);

        $response = $this->getJson("/api/shipments/track/TRACK123");

        $response->assertStatus(200)
            ->assertJsonPath('data.tracking_code', 'TRACK123');
    }
}
