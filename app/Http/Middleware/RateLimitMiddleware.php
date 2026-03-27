<?php
namespace App\Http\Middleware;

use Closure;
use App\RateLimiting\Services\RateLimitService;

class RateLimitMiddleware
{
    public function __construct(private RateLimitService $limiter) {}

    public function handle($request, Closure $next, string $strategy = 'moderate')
    {
        if (!$this->limiter->attempt($request, $strategy)) {
            return response()->json([
                'message' => 'Too many requests',
            ], 429);
        }

        return $next($request)->header(
            'X-RateLimit-Remaining',
            $this->limiter->getRemaining($request, $strategy)
        );
    }
}
