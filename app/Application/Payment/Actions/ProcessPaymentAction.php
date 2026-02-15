<?php

namespace App\Application\Payment\Actions;

use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Payment\PaymentTransitionGuard;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Payment\Services\PaymentGatewayInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Application\Payment\DTO\CreatePaymentData;
use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;
use App\Infrastructure\Notifications\Booking\BookingConfirmedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcessPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayInterface $gatewayService,
        private readonly PaymentTransitionGuard $paymentTransitionGuard,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ResolvePaymentIdempotencyAction $resolvePaymentIdempotencyAction,
        private readonly AttachPaymentToIdempotencyRecordAction $attachPaymentToIdempotencyRecordAction,
        private readonly AuthorizeBookingPaymentAction $authorizeBookingPaymentAction,
        private readonly EnsureBookingPayableAction $ensureBookingPayableAction,
        private readonly EnsureTicketInventoryForBookingAction $ensureTicketInventoryForBookingAction,
    ) {
    }

    public function execute(User $user, CreatePaymentData $data): Payment
    {
        $idempotencyRecord = $this->resolvePaymentIdempotencyAction->execute($user, $data);
        if ($idempotencyRecord?->payment_id !== null) {
            $existingPayment = $this->paymentRepository->findWithBooking((int) $idempotencyRecord->payment_id);

            if ($existingPayment !== null) {
                return $existingPayment;
            }
        }

        try {
            $notificationPayload = null;
            $payment = DB::transaction(function () use ($data, $user, $idempotencyRecord, &$notificationPayload): Payment {
                $booking = $this->bookingRepository->findForUpdate($data->bookingId);

                if ($booking === null) {
                    throw new DomainException(DomainError::BOOKING_NOT_FOUND);
                }

                $this->authorizeBookingPaymentAction->execute($user, $booking);
                $this->ensureBookingPayableAction->execute($booking);

                $ticket = $this->ticketRepository->findForUpdateWithEvent($booking->ticket_id);

                if ($ticket === null) {
                    throw new DomainException(DomainError::TICKET_NOT_FOUND);
                }

                $this->ensureTicketInventoryForBookingAction->execute($booking, $ticket);

                $amount = round(((float) $ticket->price) * (int) $booking->quantity, 2);
                $processed = $this->gatewayService->process($booking, $data->forceSuccess);

                if ($processed) {
                    $ticket->quantity = $ticket->quantity - $booking->quantity;
                    $this->ticketRepository->save($ticket);

                    $booking->status = BookingStatus::CONFIRMED;
                    $this->bookingRepository->save($booking);

                    $notificationPayload = [
                        'booking_id' => $booking->id,
                        'event_title' => $ticket->event?->title,
                        'ticket_type' => $ticket->type,
                        'quantity' => (int) $booking->quantity,
                    ];

                    $paymentStatus = PaymentStatus::SUCCESS;
                } else {
                    $booking->status = BookingStatus::CANCELLED;
                    $this->bookingRepository->save($booking);
                    $paymentStatus = PaymentStatus::FAILED;
                }

                $payment = $this->paymentRepository->create($booking, $amount, $paymentStatus);
                if ($idempotencyRecord !== null) {
                    $this->attachPaymentToIdempotencyRecordAction->execute($idempotencyRecord, (int) $payment->id);
                }

                return $payment;
            });

            if ($this->paymentTransitionGuard->canNotifyCustomer($payment->status) && is_array($notificationPayload)) {
                $booking = $payment->booking->load('user');
                $bookingUser = $booking->user;

                if ($bookingUser instanceof User) {
                    $bookingUser->notify(new BookingConfirmedNotification(
                        $notificationPayload['booking_id'],
                        $notificationPayload['event_title'],
                        $notificationPayload['ticket_type'],
                        $notificationPayload['quantity'],
                    ));
                }
            }

            return $payment->load('booking');
        } catch (QueryException $exception) {
            if ($this->isDuplicatePaymentException($exception)) {
                throw new DomainException(DomainError::PAYMENT_ALREADY_EXISTS);
            }

            throw $exception;
        }
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
