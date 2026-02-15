<?php

namespace App\Domain\Payment\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PaymentStatus|string $status
 * @property float $amount
 */
class Payment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'amount' => 'float',
            'status' => PaymentStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
