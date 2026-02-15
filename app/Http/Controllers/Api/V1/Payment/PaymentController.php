<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Application\Contracts\Services\PaymentTransactionServiceInterface;
use App\Application\Payment\DTO\CreatePaymentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\CreatePaymentRequest;
use App\Http\Requests\Api\V1\Payment\ShowPaymentRequest;
use App\Http\Resources\Api\V1\Payment\PaymentResource;
use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Models\Payment;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentTransactionServiceInterface $paymentService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function store(Booking $booking, CreatePaymentRequest $request): JsonResponse
    {
        $forceSuccess = $request->input('force_success') === null
            ? null
            : $request->boolean('force_success');
        $idempotencyKey = $request->header('Idempotency-Key');
        $idempotencyKey = is_string($idempotencyKey) && $idempotencyKey !== '' ? $idempotencyKey : null;

        $payment = $this->paymentService->process(
            $request->user(),
            CreatePaymentData::fromInput($booking->id, $forceSuccess, $idempotencyKey)
        );

        return $this->responder->created(PaymentResource::make($payment), 'Payment processed successfully');
    }

    public function show(ShowPaymentRequest $request, Payment $payment): JsonResponse
    {
        return $this->responder->success(PaymentResource::make($payment), 'OK');
    }
}
