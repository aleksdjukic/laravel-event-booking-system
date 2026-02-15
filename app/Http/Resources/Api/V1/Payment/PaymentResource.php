<?php

namespace App\Http\Resources\Api\V1\Payment;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Http\Resources\Api\V1\Booking\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status instanceof PaymentStatus ? $this->status->value : (string) $this->status;

        return [
            'id' => $this->{Payment::COL_ID},
            'booking_id' => $this->{Payment::COL_BOOKING_ID},
            'amount' => $this->{Payment::COL_AMOUNT},
            'status' => $status,
            'booking' => new BookingResource($this->whenLoaded(Payment::REL_BOOKING)),
            'created_at' => $this->{Payment::COL_CREATED_AT},
            'updated_at' => $this->{Payment::COL_UPDATED_AT},
        ];
    }
}
