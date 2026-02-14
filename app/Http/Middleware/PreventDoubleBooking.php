<?php

namespace App\Http\Middleware;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Http\ApiResponder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    public function __construct(private readonly ApiResponder $responder)
    {
    }

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ticketParam = $request->route('ticket');
        $ticketId = is_object($ticketParam) ? (int) $ticketParam->id : (int) $ticketParam;
        $user = $request->user();

        $hasActiveBooking = Booking::query()
            ->where('user_id', $user->id)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', [
                BookingStatus::PENDING->value,
                BookingStatus::CONFIRMED->value,
            ])
            ->exists();

        if ($hasActiveBooking) {
            return $this->responder->error('You already have an active booking for this ticket.', 409);
        }

        return $next($request);
    }
}
