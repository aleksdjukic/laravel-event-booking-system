<?php

namespace Tests\Feature\Modules\Event;

use App\Modules\Auth\Application\DTO\LoginData;
use App\Modules\Event\Application\DTO\CreateEventData;
use App\Modules\Event\Domain\Support\EventCache;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class EventFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_events_index_returns_422_for_invalid_date_filter(): void
    {
        $this->getJson('/api/v1/events?date=not-a-date')
            ->assertStatus(422);
    }

    public function test_events_index_supports_search_and_location_filter_and_pagination_shape(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'event.filter.organizer@example.com');

        $this->postJson('/api/v1/auth/login', [
            LoginData::INPUT_EMAIL => $organizer->{User::COL_EMAIL},
            LoginData::INPUT_PASSWORD => 'password123',
        ]);

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Tech Summit',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-09-01 10:00:00',
            CreateEventData::INPUT_LOCATION => 'Belgrade',
        ])->assertStatus(201);

        $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Music Night',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-09-02 10:00:00',
            CreateEventData::INPUT_LOCATION => 'Novi Sad',
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
        Cache::put(EventCache::INDEX_VERSION_KEY, 1);

        $organizer = $this->createUser(Role::ORGANIZER, 'event.cache.organizer@example.com');
        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Cache Event',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-09-03 10:00:00',
            CreateEventData::INPUT_LOCATION => 'Nis',
        ])->assertStatus(201);

        $this->assertGreaterThanOrEqual(2, (int) Cache::get(EventCache::INDEX_VERSION_KEY));
    }
}
