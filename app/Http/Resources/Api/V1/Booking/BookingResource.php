<?php

namespace App\Http\Resources\Api\V1\Booking;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Http\Resources\Api\V1\Payment\PaymentResource;
use App\Http\Resources\Api\V1\Ticket\TicketResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Booking */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status instanceof BookingStatus ? $this->status->value : (string) $this->status;

        return [
            'id' => $this->{Booking::COL_ID},
            'user_id' => $this->{Booking::COL_USER_ID},
            'ticket_id' => $this->{Booking::COL_TICKET_ID},
            'quantity' => $this->{Booking::COL_QUANTITY},
            'status' => $status,
            'ticket' => new TicketResource($this->whenLoaded(Booking::REL_TICKET)),
            'payment' => new PaymentResource($this->whenLoaded(Booking::REL_PAYMENT)),
            'created_at' => $this->{Booking::COL_CREATED_AT},
            'updated_at' => $this->{Booking::COL_UPDATED_AT},
        ];
    }
}
