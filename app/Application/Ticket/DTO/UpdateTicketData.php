<?php

namespace App\Application\Ticket\DTO;

class UpdateTicketData
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?float $price,
        public readonly ?int $quantity,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: array_key_exists('type', $data) ? (string) $data['type'] : null,
            price: array_key_exists('price', $data) ? (float) $data['price'] : null,
            quantity: array_key_exists('quantity', $data) ? (int) $data['quantity'] : null,
        );
    }
}
