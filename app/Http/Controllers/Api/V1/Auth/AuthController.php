<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Driver;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => UserRole::from($data['role']),
        ]);

        if ($user->role === UserRole::DRIVER) {
            Driver::create([
                'user_id' => $user->id,
                'status' => \App\Enums\DriverStatus::OFFLINE,
                'verified_at' => now(),
            ]);
        }

        if ($user->role === UserRole::RESTAURANT) {
            Restaurant::create([
                'owner_id' => $user->id,
                'restaurant_category_id' => null,
                'name' => $data['business_name'] ?? $user->name.' Kitchen',
                'slug' => Str::slug(($data['business_name'] ?? $user->name).'-'.$user->id),
                'description' => null,
                'phone' => $user->phone,
                'is_open' => false,
                'minimum_order_cents' => 0,
                'latitude' => null,
                'longitude' => null,
            ]);
        }

        $device = $request->input('device_name', 'api');
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->fresh()),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        $device = $request->input('device_name', 'api');
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
