<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\AuthServiceInterface;
use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthServiceInterface $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->authService->register(RegisterData::fromArray($request->validated()));

        return $this->created([
            'user' => UserResource::make($payload['user'])->resolve(),
            'token' => $payload['token'],
        ], 'Registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login(LoginData::fromArray($request->validated()));
        if ($payload === null) {
            return $this->error('Invalid credentials.', 401);
        }

        return $this->success([
            'user' => UserResource::make($payload['user'])->resolve(),
            'token' => $payload['token'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logout successful');
    }
}
