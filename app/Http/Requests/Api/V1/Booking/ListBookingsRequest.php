<?php

namespace App\Http\Requests\Api\V1\Booking;

use App\Domain\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ListBookingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Booking::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
