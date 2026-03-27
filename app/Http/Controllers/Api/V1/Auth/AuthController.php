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

        return $this->created([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->fresh()),
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->error('Invalid credentials.', null, 422);
        }

        /** @var User $user */
        $user = Auth::user();
        $device = $request->input('device_name', 'api');
        $token = $user->createToken($device)->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'User data');
    }
}
