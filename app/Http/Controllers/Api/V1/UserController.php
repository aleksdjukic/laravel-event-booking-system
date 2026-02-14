<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly ApiResponder $responder)
    {
    }

    public function me(Request $request): JsonResponse
    {
        return $this->responder->success(UserResource::make($request->user()), 'OK');
    }
}
