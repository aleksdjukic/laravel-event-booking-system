<?php

namespace App\Http\Resources\Api\V1\Ticket;

use App\Domain\Ticket\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ticket */
class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->{Ticket::COL_ID},
            'event_id' => $this->{Ticket::COL_EVENT_ID},
            'type' => $this->{Ticket::COL_TYPE},
            'price' => $this->{Ticket::COL_PRICE},
            'quantity' => $this->{Ticket::COL_QUANTITY},
            'created_at' => $this->{Ticket::COL_CREATED_AT},
            'updated_at' => $this->{Ticket::COL_UPDATED_AT},
        ];
    }
}
