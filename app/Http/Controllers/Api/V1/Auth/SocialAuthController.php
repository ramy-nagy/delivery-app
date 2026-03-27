<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): JsonResponse
    {
        return response()->json([
            'message' => 'Social login is not configured. Wire '.$provider.' OAuth in a future release.',
        ], 501);
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        return response()->json([
            'message' => 'Social callback not implemented.',
        ], 501);
    }
}
