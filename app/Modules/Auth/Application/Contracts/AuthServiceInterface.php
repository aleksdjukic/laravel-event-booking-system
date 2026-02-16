<?php

namespace App\Modules\Auth\Application\Contracts;

use App\Modules\Auth\Application\DTO\LoginData;
use App\Modules\Auth\Application\DTO\RegisterData;
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
