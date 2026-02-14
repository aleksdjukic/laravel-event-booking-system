<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class ApiResponder
{
    public function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json($this->payload(true, $message, $this->normalizeData($data)), $status);
    }

    public function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    public function error(string $message, int $status, mixed $errors = null): JsonResponse
    {
        return response()->json($this->payload(false, $message, null, $errors), $status);
    }

    /**
     * @return array{success: bool, message: string, data: mixed, errors: mixed}
     */
    private function payload(bool $success, string $message, mixed $data, mixed $errors = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $success ? null : $errors,
        ];
    }

    private function normalizeData(mixed $data): mixed
    {
        if ($data instanceof Responsable) {
            $response = $data->toResponse(request());

            if ($response instanceof JsonResponse) {
                return $response->getData(true);
            }

            if ($response instanceof Response) {
                return json_decode($response->getContent() ?: 'null', true);
            }
        }

        return $data;
    }
}
