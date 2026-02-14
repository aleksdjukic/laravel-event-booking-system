<?php

namespace App\Contracts\Services;

use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterData;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterData $data): array;

    /**
     * @return array{user: User, token: string}|null
     */
    public function login(LoginData $data): ?array;

    public function logout(User $user): void;
}
