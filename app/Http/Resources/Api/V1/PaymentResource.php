<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
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
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'status' => $status,
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
