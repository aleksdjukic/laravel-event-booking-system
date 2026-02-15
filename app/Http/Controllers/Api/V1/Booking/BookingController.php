<?php

namespace App\Http\Controllers\Api\V1\Booking;

use App\Application\Contracts\Services\BookingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\CancelBookingRequest;
use App\Http\Requests\Api\V1\Booking\CreateBookingRequest;
use App\Http\Requests\Api\V1\Booking\ListBookingsRequest;
use App\Http\Resources\Api\V1\Booking\BookingResource;
use App\Domain\Booking\Models\Booking;
use App\Domain\Ticket\Models\Ticket;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function store(Ticket $ticket, CreateBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            $request->user(),
            $ticket->id,
            $request->toDto()
        );

        return $this->responder->created(BookingResource::make($booking), 'Booking created successfully');
    }

    public function index(ListBookingsRequest $request): JsonResponse
    {
        $bookings = $this->bookingService->listFor($request->user());

        return $this->responder->success(BookingResource::collection($bookings), 'OK');
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        $booking = $this->bookingService->cancel($booking);

        return $this->responder->success(BookingResource::make($booking), 'Booking cancelled successfully');
    }
}
