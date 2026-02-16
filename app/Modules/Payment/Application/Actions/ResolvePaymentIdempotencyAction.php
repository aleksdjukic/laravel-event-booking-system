<?php

namespace App\Modules\Payment\Application\Actions;

use App\Modules\Payment\Application\DTO\CreatePaymentData;
use App\Domain\Payment\Models\PaymentIdempotencyKey;
use App\Domain\Payment\Repositories\PaymentIdempotencyRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\User\Models\User;

class ResolvePaymentIdempotencyAction
{
    public function __construct(private readonly PaymentIdempotencyRepositoryInterface $idempotencyRepository)
    {
    }

    public function execute(User $user, CreatePaymentData $data): ?PaymentIdempotencyKey
    {
        if ($data->idempotencyKey === null) {
            return null;
        }

        $record = $this->idempotencyRepository->findForUserByKey($user->id, $data->idempotencyKey);
        if ($record !== null) {
            if ((int) $record->booking_id !== $data->bookingId) {
                throw new DomainException(DomainError::IDEMPOTENCY_KEY_REUSED);
            }

            return $record;
        }

        $createdRecord = $this->idempotencyRepository->createPending($user->id, $data->bookingId, $data->idempotencyKey);
        if ((int) $createdRecord->booking_id !== $data->bookingId) {
            throw new DomainException(DomainError::IDEMPOTENCY_KEY_REUSED);
        }

        return $createdRecord;
    }
}
