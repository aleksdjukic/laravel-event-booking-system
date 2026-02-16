<?php

namespace Tests\Feature\Modules\Shared\Http;

use App\Modules\Event\Domain\Models\Event;
use App\Modules\User\Domain\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ApiSuccessEnvelopeFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

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
        $user = $this->createUser(Role::CUSTOMER, 'success.envelope@example.com');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/me')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('errors', null)
            ->assertJsonPath('data.email', 'success.envelope@example.com');
    }

    public function test_events_index_uses_uniform_success_envelope_with_paginated_data(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'success.organizer@example.com');

        $event = new Event();
        $event->{Event::COL_TITLE} = 'Success Event';
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-10-12 10:00:00';
        $event->{Event::COL_LOCATION} = 'Belgrade';
        $event->{Event::COL_CREATED_BY} = $organizer->{\App\Modules\User\Domain\Models\User::COL_ID};
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
}
