<?php

namespace App\Http\Controllers\Api\V1\Example;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Example Controller showing best practices with ApiResponse trait
 *
 * This is a template for implementing other controllers in your application.
 */
class ExampleResourceController
{
    use ApiResponse;

    /**
     * Display a listing of resources with pagination
     * GET /api/v1/resources
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search', '');

        $query = User::query();

        // Apply search filter if provided
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        // Get paginated results
        $users = $query->paginate($perPage);

        // Return paginated response
        return $this->successPaginatedResponse(
            data: $users->items(),
            pagination: $this->getPaginationData($users),
            message: 'Users retrieved successfully'
        );
    }

    /**
     * Show a specific resource
     * GET /api/v1/resources/{id}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        return $this->successResponse(
            data: $user,
            message: 'User retrieved successfully'
        );
    }

    /**
     * Create a new resource
     * POST /api/v1/resources
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Validate input
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|unique:users,phone',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        }

        // Create resource
        try {
            $user = User::create($validated);

            return $this->createdResponse(
                data: $user,
                message: 'User created successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update a resource
     * PUT /api/v1/resources/{id}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Check authorization
        if ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
            return $this->forbiddenResponse('You cannot update this resource');
        }

        // Validate input
        try {
            $validated = $request->validate([
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'string|min:8|confirmed',
                'phone' => 'string|unique:users,phone,' . $user->id,
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        }

        // Update resource
        try {
            $user->update($validated);

            return $this->updatedResponse(
                data: $user,
                message: 'User updated successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a resource
     * DELETE /api/v1/resources/{id}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Check authorization
        if ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
            return $this->forbiddenResponse('You cannot delete this resource');
        }

        // Prevent self-deletion for admins
        if ($user->id === auth()->id() && auth()->user()->isAdmin()) {
            return $this->conflictResponse(
                message: 'Cannot delete your own admin account',
                errors: ['self_delete' => 'Admins cannot delete their own account']
            );
        }

        try {
            $user->delete();

            return $this->deletedResponse('User deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk action example - activate multiple resources
     * POST /api/v1/resources/bulk-activate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:users,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                errors: $e->errors(),
                message: 'Invalid request'
            );
        }

        try {
            $updated = User::whereIn('id', $validated['ids'])->update(['active' => true]);

            return $this->acceptedResponse(
                data: ['updated_count' => $updated],
                message: 'Users activated successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to activate users: ' . $e->getMessage());
        }
    }

    /**
     * Rate-limited endpoint example
     * POST /api/v1/resources/export
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        // Simulate processing
        return $this->acceptedResponse(
            data: [
                'job_id' => uniqid('export_'),
                'status_url' => route('export.status'),
            ],
            message: 'Export started. Check status URL for updates.'
        );
    }

    /**
     * Error handling example - resource not found
     * GET /api/v1/resources/{id}
     *
     * This is handled by Laravel's model binding,
     * but here's how to use the trait method:
     */
    public function handleNotFound(): JsonResponse
    {
        return $this->notFoundResponse('The requested resource could not be found');
    }

    /**
     * Error handling example - unauthorized access
     */
    public function handleUnauthorized(): JsonResponse
    {
        return $this->unauthorizedResponse('Your session has expired. Please login again.');
    }

    /**
     * Error handling example - rate limiting
     */
    public function handleRateLimited(): JsonResponse
    {
        return $this->tooManyRequestsResponse('You have exceeded the rate limit. Please try again later.');
    }

    /**
     * Example: Working with relationships
     * GET /api/v1/users/{userId}/orders
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserOrders(User $user, Request $request): JsonResponse
    {
        $orders = $user->customerOrders()->paginate(15);

        return $this->successPaginatedResponse(
            data: $orders->items(),
            pagination: $this->getPaginationData($orders),
            message: 'User orders retrieved successfully'
        );
    }

    /**
     * Example: Async processing
     * POST /api/v1/resources/import
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx',
            ]);

            // Dispatch job
            $job = dispatch(new \App\Jobs\ImportUsersJob($validated['file']));

            return $this->acceptedResponse(
                data: [
                    'job_id' => $job->id,
                    'status_url' => route('jobs.status', $job->id),
                ],
                message: 'Import job started'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                errors: $e->errors()
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Example: Multiple resources list
     * GET /api/v1/users/list
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $users = User::select('id', 'name', 'email')
            ->where('active', true)
            ->limit(100)
            ->get();

        return $this->listResponse(
            items: $users->toArray(),
            message: 'Users list retrieved'
        );
    }

    /**
     * Example: Statistics endpoint
     * GET /api/v1/users/stats
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('active', true)->count(),
            'inactive_users' => User::where('active', false)->count(),
            'total_orders' => 0, // \App\Models\Order::count()
        ];

        return $this->successResponse(
            data: $stats,
            message: 'Statistics retrieved'
        );
    }

    /**
     * Example: Complex response with custom data structure
     * GET /api/v1/dashboard
     *
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        return $this->customResponse([
            'status' => 'ok',
            'dashboard' => [
                'user_stats' => [
                    'total' => User::count(),
                    'today' => User::whereDate('created_at', today())->count(),
                ],
                'recent_users' => User::latest()->limit(5)->get(),
                'meta' => [
                    'generated_at' => now(),
                    'server_version' => config('app.version'),
                ],
            ],
        ], 200);
    }
}
