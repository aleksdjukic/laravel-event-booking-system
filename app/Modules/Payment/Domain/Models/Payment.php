<?php

namespace App\Modules\Payment\Domain\Models;

use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Payment\Domain\Enums\PaymentStatus;
use Database\Factories\Modules\Payment\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PaymentStatus|string $status
 * @property float $amount
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    public const TABLE = 'payments';
    public const REL_BOOKING = 'booking';
    public const COL_ID = 'id';
    public const COL_BOOKING_ID = 'booking_id';
    public const COL_AMOUNT = 'amount';
    public const COL_STATUS = 'status';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        self::COL_BOOKING_ID,
        self::COL_AMOUNT,
        self::COL_STATUS,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::COL_BOOKING_ID => 'integer',
            self::COL_AMOUNT => 'float',
            self::COL_STATUS => PaymentStatus::class,
        ];
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
