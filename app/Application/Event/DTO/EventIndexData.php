<?php

namespace App\Application\Event\DTO;

class EventIndexData
{
    public function __construct(
        public readonly int $page,
        public readonly ?string $date,
        public readonly ?string $search,
        public readonly ?string $location,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: max(1, (int) ($data['page'] ?? 1)),
            date: isset($data['date']) ? (string) $data['date'] : null,
            search: isset($data['search']) ? (string) $data['search'] : null,
            location: isset($data['location']) ? (string) $data['location'] : null,
        );
    }
}
