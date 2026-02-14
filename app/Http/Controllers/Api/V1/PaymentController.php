<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\PaymentTransactionServiceInterface;
use App\DTO\Payment\ProcessPaymentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\PaymentStoreRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentTransactionServiceInterface $paymentService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function store(Booking $booking, PaymentStoreRequest $request): JsonResponse
    {
        $forceSuccess = $request->input('force_success') === null
            ? null
            : $request->boolean('force_success');

        $payment = $this->paymentService->process(
            $request->user(),
            ProcessPaymentData::fromInput($booking->id, $forceSuccess)
        );

        return $this->responder->created(PaymentResource::make($payment), 'Payment processed successfully');
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return $this->responder->success(PaymentResource::make($payment), 'OK');
    }
}
