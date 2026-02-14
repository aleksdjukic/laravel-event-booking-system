<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\BookingStoreRequest;
use App\Models\Booking;
use App\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function store(int $id, BookingStoreRequest $request): JsonResponse
    {
        $ticket = Ticket::query()->find($id);

        if ($ticket === null) {
            return $this->error('Ticket not found.', 404);
        }

        $validated = $request->validated();

        if ($ticket->quantity <= 0) {
            return $this->error('Ticket is sold out.', 409);
        }

        if ($validated['quantity'] > $ticket->quantity) {
            return $this->error('Not enough ticket inventory.', 409);
        }

        $booking = new Booking();
        $booking->user_id = $request->user()->id;
        $booking->ticket_id = $ticket->id;
        $booking->quantity = $validated['quantity'];
        $booking->status = 'pending';
        $booking->save();

        return $this->created($booking, 'Booking created successfully');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $query = Booking::query()->with(['ticket', 'payment']);

        $userRole = $request->user()->role instanceof Role
            ? $request->user()->role->value
            : (string) $request->user()->role;

        if ($userRole === Role::CUSTOMER->value) {
            $query->where('user_id', $request->user()->id);
        }

        $bookings = $query->paginate();

        return $this->success($bookings, 'OK');
    }

    public function cancel(int $id): JsonResponse
    {
        $booking = Booking::query()->find($id);

        if ($booking === null) {
            return $this->error('Booking not found.', 404);
        }

        $this->authorize('cancel', $booking);

        if ($booking->status !== 'pending') {
            return $this->error('Only pending bookings can be cancelled.', 409);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return $this->success($booking, 'Booking cancelled successfully');
    }
}
