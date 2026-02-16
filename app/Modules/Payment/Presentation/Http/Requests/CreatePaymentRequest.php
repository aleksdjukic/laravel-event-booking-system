<?php

namespace App\Modules\Payment\Presentation\Http\Requests;

use App\Modules\Payment\Application\DTO\CreatePaymentData;
use App\Modules\Booking\Domain\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public const INPUT_IDEMPOTENCY_KEY = 'idempotency_key';
    public const INPUT_FORCE_SUCCESS = 'force_success';

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
            self::INPUT_FORCE_SUCCESS => ['nullable', 'boolean'],
            self::INPUT_IDEMPOTENCY_KEY => ['nullable', 'string', 'max:128'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $idempotencyKeyHeader = $this->header('Idempotency-Key');
        $idempotencyKey = is_string($idempotencyKeyHeader) ? trim($idempotencyKeyHeader) : null;

        $this->merge([
            self::INPUT_IDEMPOTENCY_KEY => $idempotencyKey !== '' ? $idempotencyKey : null,
        ]);
    }

    public function toDto(Booking $booking): CreatePaymentData
    {
        /** @var array{force_success?: bool|null, idempotency_key?: string|null} $validated */
        $validated = $this->validated();

        $forceSuccess = $this->input(self::INPUT_FORCE_SUCCESS) === null
            ? null
            : $this->boolean(self::INPUT_FORCE_SUCCESS);
        $idempotencyKey = $validated[self::INPUT_IDEMPOTENCY_KEY] ?? null;

        return CreatePaymentData::fromInput((int) $booking->{Booking::COL_ID}, $forceSuccess, $idempotencyKey);
    }
}
