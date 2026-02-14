<?php

namespace App\DTO\Booking;

class CreateBookingData
{
    public function __construct(public readonly int $quantity)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(quantity: (int) $data['quantity']);
    }
}
