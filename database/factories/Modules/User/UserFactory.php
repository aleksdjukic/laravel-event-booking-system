<?php

namespace Database\Factories\Modules\User;

use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            User::COL_NAME => fake()->name(),
            User::COL_EMAIL => fake()->unique()->safeEmail(),
            User::COL_PHONE => fake()->boolean(70) ? fake()->numerify('06########') : null,
            User::COL_ROLE => Role::CUSTOMER->value,
            User::COL_EMAIL_VERIFIED_AT => now(),
            User::COL_PASSWORD => static::$password ??= Hash::make('password'),
            User::COL_REMEMBER_TOKEN => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            User::COL_EMAIL_VERIFIED_AT => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            User::COL_ROLE => Role::ADMIN->value,
        ]);
    }

    public function organizer(): static
    {
        return $this->state(fn () => [
            User::COL_ROLE => Role::ORGANIZER->value,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            User::COL_ROLE => Role::CUSTOMER->value,
        ]);
    }
}
