<?php

namespace App\Application\Payment\Actions;

use App\Domain\Booking\BookingTransitionGuard;
use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Payment\PaymentTransitionGuard;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\DTO\Payment\ProcessPaymentData;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcessPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayService $gatewayService,
        private readonly BookingTransitionGuard $bookingTransitionGuard,
        private readonly PaymentTransitionGuard $paymentTransitionGuard,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function execute(User $user, ProcessPaymentData $data): Payment
    {
        $notificationPayload = null;

        DB::beginTransaction();

        try {
            $booking = $this->bookingRepository->findForUpdate($data->bookingId);

            if ($booking === null) {
                throw new DomainException(DomainError::BOOKING_NOT_FOUND);
            }

            $this->ensureCanProcess($user, $booking);
            $this->ensureBookingCanBePaid($booking);

            $ticket = $this->ticketRepository->findForUpdate($booking->ticket_id);

            if ($ticket === null) {
                throw new DomainException(DomainError::TICKET_NOT_FOUND);
            }

            $this->ensureInventory($booking, $ticket);

            $amount = round(((float) $ticket->price) * (int) $booking->quantity, 2);
            $processed = $this->gatewayService->process($booking, $data->forceSuccess);

            if ($processed) {
                $ticket->quantity = $ticket->quantity - $booking->quantity;
                $this->ticketRepository->save($ticket);

                $booking->status = BookingStatus::CONFIRMED;
                $this->bookingRepository->save($booking);

                $notificationPayload = [
                    'booking_id' => $booking->id,
                    'event_title' => Event::query()->whereKey($ticket->event_id)->value('title'),
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
            DB::commit();

            if ($this->paymentTransitionGuard->canNotifyCustomer($payment->status) && is_array($notificationPayload)) {
                $booking = $booking->load('user');
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
        } catch (DomainException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (QueryException $exception) {
            DB::rollBack();

            if ($this->isDuplicatePaymentException($exception)) {
                throw new DomainException(DomainError::PAYMENT_ALREADY_EXISTS);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function ensureCanProcess(User $user, Booking $booking): void
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::CUSTOMER->value && $booking->user_id !== $user->id) {
            throw new DomainException(DomainError::FORBIDDEN);
        }
    }

    private function ensureBookingCanBePaid(Booking $booking): void
    {
        $bookingStatus = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from((string) $booking->status);

        if (! $this->bookingTransitionGuard->canPay($bookingStatus)) {
            throw new DomainException(DomainError::INVALID_BOOKING_STATE_FOR_PAYMENT);
        }

        if ($this->paymentRepository->existsForBooking($booking->id)) {
            throw new DomainException(DomainError::PAYMENT_ALREADY_EXISTS);
        }
    }

    private function ensureInventory(Booking $booking, Ticket $ticket): void
    {
        if ($ticket->quantity <= 0) {
            throw new DomainException(DomainError::TICKET_SOLD_OUT);
        }

        if ($booking->quantity > $ticket->quantity) {
            throw new DomainException(DomainError::NOT_ENOUGH_TICKET_INVENTORY);
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
