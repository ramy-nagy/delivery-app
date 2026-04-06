<?php

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\FCMRepository;
use App\Services\FCMService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FCMNotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private FCMRepository $fcmRepository,
        private FCMService $fcmService,
    ) {}

    /**
     * Register or update device token for current user.
     * POST /api/fcm/register
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|min:100',
            'device_id' => 'nullable|string',
            'device_type' => 'nullable|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $deviceToken = $this->fcmRepository->registerDevice($user, $validated['fcm_token'], $validated);

        return $this->createdResponse(
            data: [
                'device_token_id' => $deviceToken->id,
                'fcm_token' => $deviceToken->fcm_token,
                'device_type' => $deviceToken->device_type,
                'registered_at' => $deviceToken->last_registered_at,
            ],
            message: 'Device registered successfully'
        );
    }

    /**
     * Get all device tokens for current user.
     * GET /api/fcm/devices
     *
     * @return JsonResponse
     */
    public function getDeviceTokens(): JsonResponse
    {
        $user = Auth::user();
        $tokens = $this->fcmRepository->getUserAllTokens($user);

        return $this->successResponse(
            data: [
                'total' => $tokens->count(),
                'active' => $tokens->where('is_active', true)->count(),
                'tokens' => $tokens->map(fn ($token) => [
                    'id' => $token->id,
                    'device_type' => $token->device_type,
                    'device_name' => $token->device_name,
                    'app_version' => $token->app_version,
                    'is_active' => $token->is_active,
                    'last_used_at' => $token->last_used_at,
                    'last_registered_at' => $token->last_registered_at,
                    'created_at' => $token->created_at,
                ])->values(),
            ],
            message: 'Devices retrieved successfully'
        );
    }

    /**
     * Unregister/remove a device token.
     * DELETE /api/fcm/devices/{tokenId}
     *
     * @param Request $request
     * @param int $tokenId
     * @return JsonResponse
     */
    public function removeDeviceToken(Request $request, int $tokenId): JsonResponse
    {
        $user = Auth::user();
        $token = $user->deviceTokens()->find($tokenId);

        if (!$token) {
            return $this->notFoundResponse('Device token not found');
        }

        $token->delete();

        return $this->deletedResponse('Device token removed successfully');
    }

    /**
     * Logout all devices (remove all tokens).
     * POST /api/fcm/logout-all-devices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $user = Auth::user();
        $removed = $this->fcmRepository->removeAllUserTokens($user);

        return $this->successResponse(
            data: ['tokens_removed' => $removed],
            message: 'All devices logged out'
        );
    }

    /**
     * Subscribe device to a topic.
     * POST /api/fcm/subscribe
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function subscribeTopic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'topic' => 'required|string',
        ]);

        if (!FCMService::isValidTopic($validated['topic'])) {
            return $this->unprocessableResponse('Invalid topic name format');
        }

        try {
            $result = $this->fcmService->subscribeToTopic($validated['topic'], [$validated['fcm_token']]);

            // Also update local database
            $this->fcmRepository->subscribeToTopic($validated['fcm_token'], $validated['topic']);

            return $this->successResponse(
                data: $result,
                message: 'Subscribed to topic successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to subscribe to topic: ' . $e->getMessage());
        }
    }

    /**
     * Unsubscribe device from a topic.
     * POST /api/fcm/unsubscribe
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function unsubscribeTopic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'topic' => 'required|string',
        ]);

        if (!FCMService::isValidTopic($validated['topic'])) {
            return $this->unprocessableResponse('Invalid topic name format');
        }

        try {
            $result = $this->fcmService->unsubscribeFromTopic($validated['topic'], [$validated['fcm_token']]);

            // Also update local database
            $this->fcmRepository->unsubscribeFromTopic($validated['fcm_token'], $validated['topic']);

            return $this->successResponse(
                data: $result,
                message: 'Unsubscribed from topic successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to unsubscribe from topic: ' . $e->getMessage());
        }
    }

    /**
     * Get user's device tokens (Admin/Staff only).
     * GET /api/admin/fcm/users/{userId}/devices
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserDeviceTokens(int $userId): JsonResponse
    {
        $tokens = $this->fcmRepository->getUserActiveTokens($userId);

        return $this->successResponse(
            data: [
                'user_id' => $userId,
                'total' => $tokens->count(),
                'tokens' => $tokens->pluck('fcm_token')->toArray(),
            ],
            message: 'User device tokens retrieved'
        );
    }

    /**
     * Get FCM statistics (Admin/Staff only).
     * GET /api/admin/fcm/stats
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->fcmRepository->getDeviceStats();

        return $this->successResponse(
            data: $stats,
            message: 'FCM statistics retrieved'
        );
    }

    /**
     * Send test notification to current user's devices.
     * POST /api/fcm/test
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'body' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $tokens = $this->fcmRepository->getUserActiveTokens($user);

        if ($tokens->isEmpty()) {
            return $this->notFoundResponse('No active devices found');
        }

        try {
            $result = $this->fcmService->sendToTokens(
                $tokens->pluck('fcm_token')->toArray(),
                $request->input('title', 'Test Notification'),
                $request->input('body', 'This is a test notification from ' . config('app.name'))
            );

            return $this->successResponse(
                data: $result,
                message: 'Test notifications sent'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send test notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a topic.
     * POST /api/fcm/send-to-topic
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function sendToTopic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        if (!FCMService::isValidTopic($validated['topic'])) {
            return $this->unprocessableResponse('Invalid topic name format');
        }

        try {
            $messageId = $this->fcmService->sendToTopic(
                $validated['topic'],
                $validated['title'],
                $validated['body'],
                $validated['data'] ?? [],
                $validated['options'] ?? []
            );

            return $this->successResponse(
                data: [
                    'topic' => $validated['topic'],
                    'message_id' => $messageId,
                    'title' => $validated['title'],
                ],
                message: 'Notification sent to topic successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send notification to topic: ' . $e->getMessage());
        }
    }
}
