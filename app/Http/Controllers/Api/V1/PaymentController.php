<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\PaymentStoreRequest;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Notifications\BookingConfirmedNotification;
use App\Services\PaymentService;
use App\Support\Http\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function store(int $id, PaymentStoreRequest $request): JsonResponse
    {
        $forceSuccess = $request->input('force_success') === null
            ? null
            : $request->boolean('force_success');
        $notificationPayload = null;

        DB::beginTransaction();

        try {
            $booking = Booking::query()->whereKey($id)->lockForUpdate()->first();

            if ($booking === null) {
                DB::rollBack();

                return $this->error('Booking not found.', 404);
            }

            $userRole = $request->user()->role instanceof Role
                ? $request->user()->role->value
                : (string) $request->user()->role;

            if ($userRole === Role::CUSTOMER->value && $booking->user_id !== $request->user()->id) {
                DB::rollBack();

                return $this->error('Forbidden', 403);
            }

            if ($booking->status !== 'pending') {
                DB::rollBack();

                return $this->error('Invalid booking state for payment.', 409);
            }

            $paymentExists = Payment::query()->where('booking_id', $booking->id)->exists();
            if ($paymentExists) {
                DB::rollBack();

                return $this->error('Payment already exists for this booking.', 409);
            }

            $ticket = Ticket::query()->whereKey($booking->ticket_id)->lockForUpdate()->first();

            if ($ticket === null) {
                DB::rollBack();

                return $this->error('Ticket not found.', 404);
            }

            if ($ticket->quantity <= 0) {
                DB::rollBack();

                return $this->error('Ticket is sold out.', 409);
            }

            if ($booking->quantity > $ticket->quantity) {
                DB::rollBack();

                return $this->error('Not enough ticket inventory.', 409);
            }

            $amount = number_format(((float) $ticket->price) * (int) $booking->quantity, 2, '.', '');
            $processed = $this->paymentService->process($booking, $forceSuccess);

            if ($processed) {
                $ticket->quantity = $ticket->quantity - $booking->quantity;
                $ticket->save();

                $booking->status = 'confirmed';
                $booking->save();

                $notificationPayload = [
                    'booking_id' => $booking->id,
                    'event_title' => Event::query()->whereKey($ticket->event_id)->value('title'),
                    'ticket_type' => $ticket->type,
                    'quantity' => (int) $booking->quantity,
                ];

                $payment = new Payment();
                $payment->booking_id = $booking->id;
                $payment->amount = $amount;
                $payment->status = 'success';
                $payment->save();
            } else {
                $booking->status = 'cancelled';
                $booking->save();

                $payment = new Payment();
                $payment->booking_id = $booking->id;
                $payment->amount = $amount;
                $payment->status = 'failed';
                $payment->save();
            }

            DB::commit();

            if ($payment->status === 'success' && is_array($notificationPayload)) {
                $booking->load('user');
                $booking->user?->notify(new BookingConfirmedNotification(
                    $notificationPayload['booking_id'],
                    $notificationPayload['event_title'],
                    $notificationPayload['ticket_type'],
                    $notificationPayload['quantity'],
                ));
            }

            return $this->created($payment->load('booking'), 'Payment processed successfully');
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicatePaymentException($exception)) {
                return $this->error('Payment already exists for this booking.', 409);
            }

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::query()->with('booking')->find($id);

        if ($payment === null) {
            return $this->error('Payment not found.', 404);
        }

        $this->authorize('view', $payment);

        return $this->success($payment, 'OK');
    }

    private function isDuplicatePaymentException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'payments_booking_id_unique')) {
            return true;
        }

        $hasBookingIdColumn = str_contains($message, 'payments.booking_id')
            || str_contains($message, '`booking_id`')
            || str_contains($message, ' booking_id ');
        $hasUniqueHint = str_contains($message, 'unique') || str_contains($message, 'duplicate');

        return $hasBookingIdColumn && $hasUniqueHint;
    }
}
