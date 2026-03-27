<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDriverStatusRequest;
use App\Http\Requests\Driver\UpdateLocationRequest;
use App\Http\Resources\V1\DriverResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    public function update(UpdateLocationRequest $request): DriverResource|JsonResponse
    {
        $driver = $request->user()->driver;

        if ($driver === null) {
            return response()->json(['message' => 'Driver profile not found.'], 404);
        }

        $driver->update([
            'last_latitude' => $request->validated('latitude'),
            'last_longitude' => $request->validated('longitude'),
        ]);

        return new DriverResource($driver->fresh());
    }

    public function updateStatus(UpdateDriverStatusRequest $request): DriverResource|JsonResponse
    {
        $driver = $request->user()->driver;

        if ($driver === null) {
            return response()->json(['message' => 'Driver profile not found.'], 404);
        }

        $driver->update(['status' => $request->validated('status')]);

        return new DriverResource($driver->fresh());
    }
}
