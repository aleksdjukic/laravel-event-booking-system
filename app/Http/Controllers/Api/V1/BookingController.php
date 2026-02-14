<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\BookingServiceInterface;
use App\DTO\Booking\CreateBookingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\BookingStoreRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BookingServiceInterface $bookingService)
    {
    }

    public function store(Ticket $ticket, BookingStoreRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            $request->user(),
            $ticket->id,
            CreateBookingData::fromArray($request->validated())
        );

        return $this->created(BookingResource::make($booking)->resolve(), 'Booking created successfully');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = $this->bookingService->listFor($request->user());

        return $this->success(
            BookingResource::collection($bookings)->response()->getData(true),
            'OK'
        );
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $booking = $this->bookingService->cancel($booking);

        return $this->success(BookingResource::make($booking)->resolve(), 'Booking cancelled successfully');
    }
}
