<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Modules\Payment\Application\Contracts\PaymentTransactionServiceInterface;
use App\Modules\Shared\Presentation\Http\Controllers\ApiController;
use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Models\Payment;
use App\Modules\Payment\Presentation\Http\Requests\CreatePaymentRequest;
use App\Modules\Payment\Presentation\Http\Requests\ShowPaymentRequest;
use App\Modules\Payment\Presentation\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;

class PaymentController extends ApiController
{
    public function __construct(private readonly PaymentTransactionServiceInterface $paymentService)
    {
    }

    public function store(Booking $booking, CreatePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->process(
            $request->user(),
            $request->toDto($booking)
        );

        return $this->created(PaymentResource::make($payment), 'Payment processed successfully');
    }

    public function show(ShowPaymentRequest $request, Payment $payment): JsonResponse
    {
        return $this->success(PaymentResource::make($payment), 'OK');
    }
}
