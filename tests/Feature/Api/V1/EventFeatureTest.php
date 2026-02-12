<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_returns_422_for_invalid_date_filter(): void
    {
        $this->getJson('/api/v1/events?date=not-a-date')
            ->assertStatus(422);
    }
}
