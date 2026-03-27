<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): ReviewResource|JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()->findOrFail($data['order_id']);

        if ((int) $order->customer_id !== (int) $user->id) {
            abort(403, 'You cannot review this order.');
        }

        if ($order->status !== OrderStatus::DELIVERED) {
            return $this->error('You can only review delivered orders.', null, 422);
        }

        if (Review::query()->where('order_id', $order->id)->where('user_id', $user->id)->exists()) {
            return $this->error('You already reviewed this order.', null, 422);
        }

        $review = Review::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'restaurant_id' => $order->restaurant_id,
            'driver_id' => $order->driver_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return new ReviewResource($review);
    }
}
