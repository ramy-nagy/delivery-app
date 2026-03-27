<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $driver = $user?->driver;

        if ($driver === null || $driver->verified_at === null) {
            return response()->json(['message' => 'Driver profile is missing or not verified.'], 403);
        }

        return $next($request);
    }
}
