<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\Auth\DTO\LoginData;
use App\Application\Auth\DTO\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->authService->register(RegisterData::fromArray($request->validated()));

        return $this->responder->created([
            'user' => UserResource::make($payload['user']),
            'token' => $payload['token'],
        ], 'Registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login(LoginData::fromArray($request->validated()));
        if ($payload === null) {
            return $this->responder->error('Invalid credentials.', 401);
        }

        return $this->responder->success([
            'user' => UserResource::make($payload['user']),
            'token' => $payload['token'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->responder->success(null, 'Logout successful');
    }
}
