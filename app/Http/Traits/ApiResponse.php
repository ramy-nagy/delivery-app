<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response.
     */
    public function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a created response (201).
     */
    public function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return an error response.
     */
    public function error(string $message = 'Error', mixed $data = null, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a not found response (404).
     */
    public function notFound(string $message = 'Not found'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    /**
     * Return an unauthorized response (401).
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    /**
     * Return a forbidden response (403).
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    /**
     * Return a validation error response (422).
     */
    public function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Return a server error response (500).
     */
    public function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, null, 500);
    }
}
