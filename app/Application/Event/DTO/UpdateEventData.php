<?php

namespace App\Application\Event\DTO;

class UpdateEventData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $date,
        public readonly string $location,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            date: (string) $data['date'],
            location: (string) $data['location'],
        );
    }
}
