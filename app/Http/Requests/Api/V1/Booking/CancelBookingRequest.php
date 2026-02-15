<?php

namespace App\Http\Requests\Api\V1\Booking;

use App\Domain\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking instanceof Booking && ($this->user()?->can('cancel', $booking) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
