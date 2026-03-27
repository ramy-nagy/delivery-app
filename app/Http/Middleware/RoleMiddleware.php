<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowed = array_map(
            static fn (string $r) => UserRole::from($r),
            $roles
        );

        if (! in_array($user->role, $allowed, true)) {
            return response()->json(['message' => 'Forbidden for this role.'], 403);
        }

        return $next($request);
    }
}
