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
    public const TABLE = 'bookings';
    public const REL_USER = 'user';
    public const REL_TICKET = 'ticket';
    public const REL_PAYMENT = 'payment';
    public const COL_ID = 'id';
    public const COL_USER_ID = 'user_id';
    public const COL_TICKET_ID = 'ticket_id';
    public const COL_QUANTITY = 'quantity';
    public const COL_STATUS = 'status';
    public const COL_ACTIVE_BOOKING_KEY = 'active_booking_key';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        self::COL_USER_ID,
        self::COL_TICKET_ID,
        self::COL_QUANTITY,
        self::COL_STATUS,
        self::COL_ACTIVE_BOOKING_KEY,
    ];

    protected static function booted(): void
    {
        static::saving(function (Booking $booking): void {
            $status = $booking->status instanceof BookingStatus
                ? $booking->status
                : BookingStatus::from((string) $booking->status);

            $booking->{self::COL_ACTIVE_BOOKING_KEY} = $status->isActive()
                ? ((int) $booking->{self::COL_USER_ID}).':'.((int) $booking->{self::COL_TICKET_ID})
                : null;
        });
    }

    protected function casts(): array
    {
        return [
            self::COL_USER_ID => 'integer',
            self::COL_TICKET_ID => 'integer',
            self::COL_QUANTITY => 'integer',
            self::COL_STATUS => BookingStatus::class,
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
