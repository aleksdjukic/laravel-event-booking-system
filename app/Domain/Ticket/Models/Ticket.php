<?php

namespace App\Domain\Ticket\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @property float $price
 */
class Ticket extends Model
{
    public const TABLE = 'tickets';
    public const REL_EVENT = 'event';
    public const REL_BOOKINGS = 'bookings';
    public const COL_ID = 'id';
    public const COL_EVENT_ID = 'event_id';
    public const COL_TYPE = 'type';
    public const COL_PRICE = 'price';
    public const COL_QUANTITY = 'quantity';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        self::COL_EVENT_ID,
        self::COL_TYPE,
        self::COL_PRICE,
        self::COL_QUANTITY,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::COL_EVENT_ID => 'integer',
            self::COL_PRICE => 'float',
            self::COL_QUANTITY => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
