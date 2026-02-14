<?php

namespace App\DTO\Ticket;

class CreateTicketData
{
    public function __construct(
        public readonly string $type,
        public readonly float $price,
        public readonly int $quantity,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) $data['type'],
            price: (float) $data['price'],
            quantity: (int) $data['quantity'],
        );
    }
}
