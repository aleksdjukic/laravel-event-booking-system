<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiErrorEnvelopeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_uses_uniform_envelope(): void
    {
        $response = $this->getJson('/api/v1/events?date=not-a-date');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
            ]);
    }

    public function test_authorization_error_uses_uniform_envelope(): void
    {
        $customer = $this->createUser('customer', 'envelope.customer@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Blocked',
            'description' => null,
            'date' => '2026-10-01 12:00:00',
            'location' => 'Belgrade',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden')
            ->assertJsonPath('data', null);
    }

    private function createUser(string $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = $role;
        $user->save();

        return $user;
    }
}
