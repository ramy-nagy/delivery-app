<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDriverProfileRequest;
use App\Http\Resources\V1\DriverResource;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverProfileController extends Controller
{
    public function show(Request $request): DriverResource|JsonResponse
    {
        $driver = $request->user()->driver;

        if ($driver === null) {
            return $this->notFound('Driver profile not found.');
        }

        return new DriverResource($driver->load('user'));
    }

    public function update(UpdateDriverProfileRequest $request): DriverResource|JsonResponse
    {
        $user = $request->user();
        $driver = $user->driver ?? new Driver(['user_id' => $user->id]);

        if (! $driver->exists) {
            $driver->status = \App\Enums\DriverStatus::OFFLINE;
            $driver->verified_at = now();
            $driver->save();
        }

        $driver->fill($request->validated());
        $driver->save();

        return new DriverResource($driver->load('user'));
    }
}
