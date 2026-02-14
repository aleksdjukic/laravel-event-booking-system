<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Contracts\Services\BookingServiceInterface;
use App\Application\Booking\DTO\CreateBookingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\BookingStoreRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Domain\Booking\Models\Booking;
use App\Domain\Ticket\Models\Ticket;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function store(Ticket $ticket, BookingStoreRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            $request->user(),
            $ticket->id,
            CreateBookingData::fromArray($request->validated())
        );

        return $this->responder->created(BookingResource::make($booking), 'Booking created successfully');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = $this->bookingService->listFor($request->user());

        return $this->responder->success(BookingResource::collection($bookings), 'OK');
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $booking = $this->bookingService->cancel($booking);

        return $this->responder->success(BookingResource::make($booking), 'Booking cancelled successfully');
    }
}
