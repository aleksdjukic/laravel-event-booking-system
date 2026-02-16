<?php

namespace Tests\Feature\Modules\Shared\Http;

use App\Modules\Event\Application\DTO\CreateEventData;
use App\Modules\User\Domain\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ApiErrorEnvelopeFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

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
        $customer = $this->createUser(Role::CUSTOMER, 'envelope.customer@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Blocked',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-10-01 12:00:00',
            CreateEventData::INPUT_LOCATION => 'Belgrade',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden')
            ->assertJsonPath('data', null);
    }

    public function test_unauthenticated_error_uses_uniform_envelope(): void
    {
        $this->getJson('/api/v1/user/me')
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized')
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }

    public function test_not_found_error_uses_uniform_envelope(): void
    {
        $this->getJson('/api/v1/unknown-endpoint')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not found')
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }
}
