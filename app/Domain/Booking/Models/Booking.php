<?php

namespace App\Domain\Booking\Models;

use App\Domain\Payment\Models\Payment;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Models\User;
use App\Domain\Booking\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

/**
 * @property BookingStatus|string $status
 * @property string|null $active_booking_key
 */
class Booking extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Booking $booking): void {
            $status = $booking->status instanceof BookingStatus
                ? $booking->status
                : BookingStatus::from((string) $booking->status);

            $booking->active_booking_key = $status->isActive()
                ? ((int) $booking->user_id).':'.((int) $booking->ticket_id)
                : null;
        });
    }

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
