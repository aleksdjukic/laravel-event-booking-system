<?php

namespace Tests\Feature\Api\V1;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_returns_422_for_invalid_date_filter(): void
    {
        $this->getJson('/api/v1/events?date=not-a-date')
            ->assertStatus(422);
    }

    public function test_events_index_supports_search_and_location_filter_and_pagination_shape(): void
    {
        $organizer = $this->createUser('organizer', 'event.filter.organizer@example.com');

        $this->postJson('/api/v1/auth/login', [
            'email' => $organizer->email,
            'password' => 'password123',
        ]);

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            'title' => 'Tech Summit',
            'description' => null,
            'date' => '2026-09-01 10:00:00',
            'location' => 'Belgrade',
        ])->assertStatus(201);

        $this->postJson('/api/v1/events', [
            'title' => 'Music Night',
            'description' => null,
            'date' => '2026-09-02 10:00:00',
            'location' => 'Novi Sad',
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/events?search=Tech&location=Belgrade&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['data', 'links', 'meta'],
                'errors',
            ]);
    }

    public function test_event_creation_bumps_cache_version(): void
    {
        Cache::put('events:index:version', 1);

        $organizer = $this->createUser('organizer', 'event.cache.organizer@example.com');
        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            'title' => 'Cache Event',
            'description' => null,
            'date' => '2026-09-03 10:00:00',
            'location' => 'Nis',
        ])->assertStatus(201);

        $this->assertGreaterThanOrEqual(2, (int) Cache::get('events:index:version'));
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
