<?php

namespace App\Modules\Event\Domain\Models;

use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Models\User;
use Database\Factories\Modules\Event\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    public const TABLE = 'events';
    public const REL_USER = 'user';
    public const REL_TICKETS = 'tickets';
    public const COL_ID = 'id';
    public const COL_TITLE = 'title';
    public const COL_DESCRIPTION = 'description';
    public const COL_DATE = 'date';
    public const COL_LOCATION = 'location';
    public const COL_CREATED_BY = 'created_by';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        self::COL_TITLE,
        self::COL_DESCRIPTION,
        self::COL_DATE,
        self::COL_LOCATION,
        self::COL_CREATED_BY,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::COL_CREATED_BY => 'integer',
        ];
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_CREATED_BY);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
