<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request): JsonResponse
    {
        Log::info('Stripe webhook received', [
            'payload' => $request->all(),
        ]);

        return response()->json(['received' => true]);
    }

    public function paymob(Request $request): JsonResponse
    {
        Log::info('Paymob webhook received', [
            'payload' => $request->all(),
        ]);

        return response()->json(['received' => true]);
    }
}
