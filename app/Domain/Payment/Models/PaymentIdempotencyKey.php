<?php

namespace App\Domain\Payment\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIdempotencyKey extends Model
{
    public const TABLE = 'payment_idempotency_keys';
    public const REL_USER = 'user';
    public const REL_BOOKING = 'booking';
    public const REL_PAYMENT = 'payment';
    public const COL_ID = 'id';
    public const COL_USER_ID = 'user_id';
    public const COL_BOOKING_ID = 'booking_id';
    public const COL_IDEMPOTENCY_KEY = 'idempotency_key';
    public const COL_PAYMENT_ID = 'payment_id';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        self::COL_USER_ID,
        self::COL_BOOKING_ID,
        self::COL_IDEMPOTENCY_KEY,
        self::COL_PAYMENT_ID,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::COL_USER_ID => 'integer',
            self::COL_BOOKING_ID => 'integer',
            self::COL_PAYMENT_ID => 'integer',
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
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
