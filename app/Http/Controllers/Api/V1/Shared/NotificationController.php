<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $request->user()
            ->screenNotifications()
            ->latest()
            ->paginate(30);

        return NotificationResource::collection($items);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()
            ->screenNotifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return $this->success(null, 'Marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->screenNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'All marked as read.');
    }
}
