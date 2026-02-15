<?php

namespace App\Http\Resources\Api\V1\Event;

use App\Domain\Event\Models\Event;
use App\Http\Resources\Api\V1\Ticket\TicketResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->{Event::COL_ID},
            'title' => $this->{Event::COL_TITLE},
            'description' => $this->{Event::COL_DESCRIPTION},
            'date' => $this->{Event::COL_DATE},
            'location' => $this->{Event::COL_LOCATION},
            'created_by' => $this->{Event::COL_CREATED_BY},
            'tickets' => TicketResource::collection($this->whenLoaded(Event::REL_TICKETS)),
            'created_at' => $this->{Event::COL_CREATED_AT},
            'updated_at' => $this->{Event::COL_UPDATED_AT},
        ];
    }
}
