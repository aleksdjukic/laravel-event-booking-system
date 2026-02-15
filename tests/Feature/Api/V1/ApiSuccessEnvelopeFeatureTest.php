<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Event\Models\Event;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSuccessEnvelopeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_ping_uses_uniform_success_envelope(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'ok')
            ->assertJsonPath('errors', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
            ]);
    }

    public function test_authenticated_profile_uses_uniform_success_envelope(): void
    {
        $user = $this->createUser('customer', 'success.envelope@example.com');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/me')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('errors', null)
            ->assertJsonPath('data.email', 'success.envelope@example.com');
    }

    public function test_events_index_uses_uniform_success_envelope_with_paginated_data(): void
    {
        $organizer = $this->createUser('organizer', 'success.organizer@example.com');

        $event = new Event();
        $event->title = 'Success Event';
        $event->description = null;
        $event->date = '2026-10-12 10:00:00';
        $event->location = 'Belgrade';
        $event->created_by = $organizer->id;
        $event->save();

        $this->getJson('/api/v1/events?page=1')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('errors', null)
            ->assertJsonPath('data.data.0.title', 'Success Event')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['data', 'links', 'meta'],
                'errors',
            ]);
    }

    private function createUser(string $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = Role::from($role);
        $user->save();

        return $user;
    }
}
