<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Successful response with data
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Successful response with pagination
     *
     * @param mixed $data
     * @param array $pagination
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successPaginatedResponse(
        mixed $data,
        array $pagination = [],
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
        ], $statusCode);
    }

    /**
     * Created response (201)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Updated response
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function updatedResponse(
        mixed $data = null,
        string $message = 'Updated successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * Deleted response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function deletedResponse(string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response (422)
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Unprocessable entity response (422)
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function unprocessableResponse(
        string $message = 'Unprocessable entity',
        mixed $errors = null
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Too many requests response (429)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function tooManyRequestsResponse(string $message = 'Too many requests'): JsonResponse
    {
        return $this->errorResponse($message, 429);
    }

    /**
     * Server error response (500)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Service unavailable response (503)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function serviceUnavailableResponse(string $message = 'Service unavailable'): JsonResponse
    {
        return $this->errorResponse($message, 503);
    }

    /**
     * Bad request response (400)
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function badRequestResponse(
        string $message = 'Bad request',
        mixed $errors = null
    ): JsonResponse {
        return $this->errorResponse($message, 400, $errors);
    }

    /**
     * Conflict response (409)
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function conflictResponse(
        string $message = 'Conflict',
        mixed $errors = null
    ): JsonResponse {
        return $this->errorResponse($message, 409, $errors);
    }

    /**
     * Build pagination array from paginated collection
     *
     * @param object $paginatedData Paginated collection from query builder or model
     * @return array
     */
    protected function getPaginationData(object $paginatedData): array
    {
        return [
            'total' => $paginatedData->total(),
            'count' => $paginatedData->count(),
            'per_page' => $paginatedData->perPage(),
            'current_page' => $paginatedData->currentPage(),
            'last_page' => $paginatedData->lastPage(),
            'from' => $paginatedData->firstItem(),
            'to' => $paginatedData->lastItem(),
            'has_more' => $paginatedData->hasMorePages(),
            'next_page_url' => $paginatedData->nextPageUrl(),
            'prev_page_url' => $paginatedData->previousPageUrl(),
        ];
    }

    /**
     * Build simple pagination array
     *
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @return array
     */
    protected function buildPagination(
        int $total,
        int $perPage = 15,
        int $currentPage = 1
    ): array {
        $lastPage = ceil($total / $perPage);
        $from = ($currentPage - 1) * $perPage + 1;
        $to = min($currentPage * $perPage, $total);

        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $from > $total ? null : $from,
            'to' => $from > $total ? null : $to,
            'has_more' => $currentPage < $lastPage,
        ];
    }

    /**
     * Response with custom structure
     *
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function customResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * No content response (204)
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Accepted response (202)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function acceptedResponse(
        mixed $data = null,
        string $message = 'Request accepted'
    ): JsonResponse {
        return $this->successResponse($data, $message, 202);
    }

    /**
     * List response with optional pagination
     *
     * @param array $items
     * @param string $message
     * @param array|null $pagination
     * @return JsonResponse
     */
    protected function listResponse(
        array $items,
        string $message = 'Success',
        ?array $pagination = null
    ): JsonResponse {
        if ($pagination) {
            return $this->successPaginatedResponse($items, $pagination, $message);
        }

        return $this->successResponse([
            'items' => $items,
            'count' => count($items),
        ], $message);
    }
}
