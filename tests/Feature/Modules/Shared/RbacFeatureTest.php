<?php

namespace Tests\Feature\Modules\Shared;

use App\Modules\Event\Application\DTO\CreateEventData;
use App\Modules\Event\Application\DTO\UpdateEventData;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Ticket\Application\DTO\CreateTicketData;
use App\Modules\Ticket\Application\DTO\UpdateTicketData;
use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class RbacFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_customer_cannot_create_event(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'rbac.customer.event@example.com');

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Blocked Event',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-09-01 12:00:00',
            CreateEventData::INPUT_LOCATION => 'Belgrade',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_create_ticket(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'rbac.customer.ticket@example.com');
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.ticket.owner@example.com');

        $event = $this->createEvent($organizer, 'RBAC Event 1');

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/events/'.$event->{Event::COL_ID}.'/tickets', [
            CreateTicketData::INPUT_TYPE => 'Standard',
            CreateTicketData::INPUT_PRICE => 50,
            CreateTicketData::INPUT_QUANTITY => 10,
        ])->assertStatus(403);
    }

    public function test_organizer_can_create_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.create.event@example.com');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            CreateEventData::INPUT_TITLE => 'Organizer Event',
            CreateEventData::INPUT_DESCRIPTION => null,
            CreateEventData::INPUT_DATE => '2026-10-01 10:00:00',
            CreateEventData::INPUT_LOCATION => 'Novi Sad',
        ])->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_organizer_can_create_ticket_for_own_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.own.ticket@example.com');
        $event = $this->createEvent($organizer, 'Organizer Own Event');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events/'.$event->{Event::COL_ID}.'/tickets', [
            CreateTicketData::INPUT_TYPE => 'VIP',
            CreateTicketData::INPUT_PRICE => 100,
            CreateTicketData::INPUT_QUANTITY => 20,
        ])->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_organizer_cannot_update_or_delete_another_organizer_event_or_ticket(): void
    {
        $organizerA = $this->createUser(Role::ORGANIZER, 'rbac.organizer.a@example.com');
        $organizerB = $this->createUser(Role::ORGANIZER, 'rbac.organizer.b@example.com');

        $event = $this->createEvent($organizerA, 'Organizer A Event');
        $ticket = $this->createTicket($event, 'Standard', 75.00, 10);

        Sanctum::actingAs($organizerB);

        $this->putJson('/api/v1/events/'.$event->{Event::COL_ID}, [
            UpdateEventData::INPUT_TITLE => 'Updated by B',
            UpdateEventData::INPUT_DESCRIPTION => null,
            UpdateEventData::INPUT_DATE => '2026-11-01 09:00:00',
            UpdateEventData::INPUT_LOCATION => 'Nis',
        ])->assertStatus(403);

        $this->deleteJson('/api/v1/events/'.$event->{Event::COL_ID})->assertStatus(403);

        $this->putJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID}, [
            UpdateTicketData::INPUT_PRICE => 85,
        ])->assertStatus(403);

        $this->deleteJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID})->assertStatus(403);
    }

    public function test_admin_can_update_and_delete_any_event_and_ticket(): void
    {
        $admin = $this->createUser(Role::ADMIN, 'rbac.admin@example.com');
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.owner@example.com');

        $event = $this->createEvent($organizer, 'Owner Event');
        $ticket = $this->createTicket($event, 'Regular', 40.00, 15);

        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/events/'.$event->{Event::COL_ID}, [
            UpdateEventData::INPUT_TITLE => 'Admin Updated Event',
            UpdateEventData::INPUT_DESCRIPTION => 'Updated',
            UpdateEventData::INPUT_DATE => '2026-12-01 10:30:00',
            UpdateEventData::INPUT_LOCATION => 'Subotica',
        ])->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->putJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID}, [
            UpdateTicketData::INPUT_QUANTITY => 12,
        ])->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->deleteJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID})->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->deleteJson('/api/v1/events/'.$event->{Event::COL_ID})->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_organizer_cannot_create_duplicate_ticket_type_for_same_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.duplicate.create@example.com');
        $event = $this->createEvent($organizer, 'Duplicate Ticket Event');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events/'.$event->{Event::COL_ID}.'/tickets', [
            CreateTicketData::INPUT_TYPE => 'VIP',
            CreateTicketData::INPUT_PRICE => 120,
            CreateTicketData::INPUT_QUANTITY => 15,
        ])->assertStatus(201);

        $this->postJson('/api/v1/events/'.$event->{Event::COL_ID}.'/tickets', [
            CreateTicketData::INPUT_TYPE => 'VIP',
            CreateTicketData::INPUT_PRICE => 150,
            CreateTicketData::INPUT_QUANTITY => 20,
        ])->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_organizer_cannot_update_ticket_to_duplicate_type_for_same_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.duplicate.update@example.com');
        $event = $this->createEvent($organizer, 'Duplicate Update Event');

        $vipTicket = $this->createTicket($event, 'VIP', 120.00, 10);
        $standardTicket = $this->createTicket($event, 'Standard', 80.00, 10);

        Sanctum::actingAs($organizer);

        $this->putJson('/api/v1/tickets/'.$standardTicket->{Ticket::COL_ID}, [
            UpdateTicketData::INPUT_TYPE => 'VIP',
        ])->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas(Ticket::TABLE, [
            Ticket::COL_ID => $vipTicket->{Ticket::COL_ID},
            Ticket::COL_TYPE => 'VIP',
        ]);
    }

    private function createEvent(User $organizer, string $title): Event
    {
        $event = new Event();
        $event->{Event::COL_TITLE} = $title;
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-08-01 12:00:00';
        $event->{Event::COL_LOCATION} = 'Belgrade';
        $event->{Event::COL_CREATED_BY} = $organizer->{User::COL_ID};
        $event->save();

        return $event;
    }

    private function createTicket(Event $event, string $type, float $price, int $quantity): Ticket
    {
        $ticket = new Ticket();
        $ticket->{Ticket::COL_EVENT_ID} = $event->{Event::COL_ID};
        $ticket->{Ticket::COL_TYPE} = $type;
        $ticket->{Ticket::COL_PRICE} = $price;
        $ticket->{Ticket::COL_QUANTITY} = $quantity;
        $ticket->save();

        return $ticket;
    }
}
