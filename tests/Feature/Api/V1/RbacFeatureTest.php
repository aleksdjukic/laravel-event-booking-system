<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RbacFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_create_event(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'rbac.customer.event@example.com');

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/events', [
            'title' => 'Blocked Event',
            'description' => null,
            'date' => '2026-09-01 12:00:00',
            'location' => 'Belgrade',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_create_ticket(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'rbac.customer.ticket@example.com');
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.ticket.owner@example.com');

        $event = $this->createEvent($organizer, 'RBAC Event 1');

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/events/'.$event->id.'/tickets', [
            'type' => 'Standard',
            'price' => 50,
            'quantity' => 10,
        ])->assertStatus(403);
    }

    public function test_organizer_can_create_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.create.event@example.com');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events', [
            'title' => 'Organizer Event',
            'description' => null,
            'date' => '2026-10-01 10:00:00',
            'location' => 'Novi Sad',
        ])->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_organizer_can_create_ticket_for_own_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.own.ticket@example.com');
        $event = $this->createEvent($organizer, 'Organizer Own Event');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events/'.$event->id.'/tickets', [
            'type' => 'VIP',
            'price' => 100,
            'quantity' => 20,
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

        $this->putJson('/api/v1/events/'.$event->id, [
            'title' => 'Updated by B',
            'description' => null,
            'date' => '2026-11-01 09:00:00',
            'location' => 'Nis',
        ])->assertStatus(403);

        $this->deleteJson('/api/v1/events/'.$event->id)->assertStatus(403);

        $this->putJson('/api/v1/tickets/'.$ticket->id, [
            'price' => 85,
        ])->assertStatus(403);

        $this->deleteJson('/api/v1/tickets/'.$ticket->id)->assertStatus(403);
    }

    public function test_admin_can_update_and_delete_any_event_and_ticket(): void
    {
        $admin = $this->createUser(Role::ADMIN, 'rbac.admin@example.com');
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.owner@example.com');

        $event = $this->createEvent($organizer, 'Owner Event');
        $ticket = $this->createTicket($event, 'Regular', 40.00, 15);

        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/events/'.$event->id, [
            'title' => 'Admin Updated Event',
            'description' => 'Updated',
            'date' => '2026-12-01 10:30:00',
            'location' => 'Subotica',
        ])->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->putJson('/api/v1/tickets/'.$ticket->id, [
            'quantity' => 12,
        ])->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->deleteJson('/api/v1/tickets/'.$ticket->id)->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->deleteJson('/api/v1/events/'.$event->id)->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_organizer_cannot_create_duplicate_ticket_type_for_same_event(): void
    {
        $organizer = $this->createUser(Role::ORGANIZER, 'rbac.organizer.duplicate.create@example.com');
        $event = $this->createEvent($organizer, 'Duplicate Ticket Event');

        Sanctum::actingAs($organizer);

        $this->postJson('/api/v1/events/'.$event->id.'/tickets', [
            'type' => 'VIP',
            'price' => 120,
            'quantity' => 15,
        ])->assertStatus(201);

        $this->postJson('/api/v1/events/'.$event->id.'/tickets', [
            'type' => 'VIP',
            'price' => 150,
            'quantity' => 20,
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

        $this->putJson('/api/v1/tickets/'.$standardTicket->id, [
            'type' => 'VIP',
        ])->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('tickets', [
            'id' => $vipTicket->id,
            'type' => 'VIP',
        ]);
    }

    private function createUser(Role $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role->value).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function createEvent(User $organizer, string $title): Event
    {
        $event = new Event();
        $event->title = $title;
        $event->description = null;
        $event->date = '2026-08-01 12:00:00';
        $event->location = 'Belgrade';
        $event->created_by = $organizer->id;
        $event->save();

        return $event;
    }

    private function createTicket(Event $event, string $type, float $price, int $quantity): Ticket
    {
        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = $type;
        $ticket->price = $price;
        $ticket->quantity = $quantity;
        $ticket->save();

        return $ticket;
    }
}
