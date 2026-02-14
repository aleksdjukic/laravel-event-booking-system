<?php

namespace App\Domain\Shared;

enum DomainError: string
{
    case FORBIDDEN = 'forbidden';
    case EVENT_NOT_FOUND = 'event_not_found';
    case TICKET_NOT_FOUND = 'ticket_not_found';
    case BOOKING_NOT_FOUND = 'booking_not_found';
    case PAYMENT_NOT_FOUND = 'payment_not_found';
    case DUPLICATE_TICKET_TYPE = 'duplicate_ticket_type';
    case TICKET_SOLD_OUT = 'ticket_sold_out';
    case NOT_ENOUGH_TICKET_INVENTORY = 'not_enough_ticket_inventory';
    case BOOKING_NOT_PENDING = 'booking_not_pending';
    case INVALID_BOOKING_STATE_FOR_PAYMENT = 'invalid_booking_state_for_payment';
    case PAYMENT_ALREADY_EXISTS = 'payment_already_exists';
    case IDEMPOTENCY_KEY_REUSED = 'idempotency_key_reused';

    public function message(): string
    {
        return match ($this) {
            self::FORBIDDEN => 'Forbidden',
            self::EVENT_NOT_FOUND => 'Event not found.',
            self::TICKET_NOT_FOUND => 'Ticket not found.',
            self::BOOKING_NOT_FOUND => 'Booking not found.',
            self::PAYMENT_NOT_FOUND => 'Payment not found.',
            self::DUPLICATE_TICKET_TYPE => 'Ticket type already exists for this event.',
            self::TICKET_SOLD_OUT => 'Ticket is sold out.',
            self::NOT_ENOUGH_TICKET_INVENTORY => 'Not enough ticket inventory.',
            self::BOOKING_NOT_PENDING => 'Only pending bookings can be cancelled.',
            self::INVALID_BOOKING_STATE_FOR_PAYMENT => 'Invalid booking state for payment.',
            self::PAYMENT_ALREADY_EXISTS => 'Payment already exists for this booking.',
            self::IDEMPOTENCY_KEY_REUSED => 'Idempotency key cannot be reused for another booking.',
        };
    }
}
