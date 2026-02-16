<?php

namespace App\Modules\User\Domain\Models;

use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Payment\Domain\Models\Payment;
use App\Modules\User\Domain\Enums\Role;
use Database\Factories\Modules\User\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * @property Role|string $role
 */
class User extends Authenticatable
{
    public const TABLE = 'users';
    public const REL_EVENTS = 'events';
    public const REL_BOOKINGS = 'bookings';
    public const REL_PAYMENTS = 'payments';
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_EMAIL = 'email';
    public const COL_PHONE = 'phone';
    public const COL_PASSWORD = 'password';
    public const COL_ROLE = 'role';
    public const COL_REMEMBER_TOKEN = 'remember_token';
    public const COL_EMAIL_VERIFIED_AT = 'email_verified_at';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    /** @use HasFactory<\Database\Factories\Modules\User\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_EMAIL,
        self::COL_PHONE,
        self::COL_PASSWORD,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        self::COL_PASSWORD,
        self::COL_REMEMBER_TOKEN,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::COL_EMAIL_VERIFIED_AT => 'datetime',
            self::COL_PASSWORD => 'hashed',
            self::COL_ROLE => Role::class,
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, Event::COL_CREATED_BY);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasManyThrough<Payment, Booking, $this>
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    public function roleValue(): string
    {
        return $this->role instanceof Role ? $this->role->value : (string) $this->role;
    }

    public function hasRole(Role|string $role): bool
    {
        $roleValue = $role instanceof Role ? $role->value : $role;

        return $this->roleValue() === $roleValue;
    }

    /**
     * @param  array<int, Role|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
