<?php

namespace App\Http\Requests\Api\V1\Payment;

use App\Application\Payment\DTO\CreatePaymentData;
use App\Domain\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'force_success' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(Booking $booking): CreatePaymentData
    {
        $forceSuccess = $this->input('force_success') === null
            ? null
            : $this->boolean('force_success');

        $idempotencyKeyHeader = $this->header('Idempotency-Key');
        $idempotencyKey = is_string($idempotencyKeyHeader) && $idempotencyKeyHeader !== ''
            ? $idempotencyKeyHeader
            : null;

        return CreatePaymentData::fromInput($booking->id, $forceSuccess, $idempotencyKey);
    }
}
