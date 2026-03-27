<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function show(string $uuid): JsonResponse
    {
        $order = Order::query()
            ->where('uuid', $uuid)
            ->with(['restaurant:id,name,slug', 'driver:id,last_latitude,last_longitude'])
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $status = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;

        return response()->json([
            'uuid' => $order->uuid,
            'status' => $status,
            'restaurant' => $order->restaurant ? [
                'name' => $order->restaurant->name,
                'slug' => $order->restaurant->slug,
            ] : null,
            'driver_location' => $order->driver ? [
                'latitude' => $order->driver->last_latitude,
                'longitude' => $order->driver->last_longitude,
            ] : null,
        ]);
    }
}
