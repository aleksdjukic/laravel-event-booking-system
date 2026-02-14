<?php

namespace App\Application\Contracts\Services;

use App\Application\Auth\DTO\LoginData;
use App\Application\Auth\DTO\RegisterData;
use App\Domain\User\Models\User;

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
