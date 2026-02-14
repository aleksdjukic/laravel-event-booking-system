<?php

namespace App\Application\Services\Auth;

use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\Auth\DTO\LoginData;
use App\Application\Auth\DTO\RegisterData;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterData $data): array
    {
        $user = new User();
        $user->name = $data->name;
        $user->email = $data->email;
        $user->phone = $data->phone;
        $user->role = Role::CUSTOMER->value;
        $user->password = Hash::make($data->password);
        $user->save();

        return [
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ];
    }

    /**
     * @return array{user: User, token: string}|null
     */
    public function login(LoginData $data): ?array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            return null;
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }
}
