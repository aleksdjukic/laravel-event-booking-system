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
    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'type',
        'price',
        'quantity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'price' => 'float',
            'quantity' => 'integer',
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
