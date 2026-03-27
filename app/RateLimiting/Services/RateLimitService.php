<?php
namespace App\RateLimiting\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class RateLimitService
{
    public function __construct(private RateLimiter $limiter) {}

    public function attempt(Request $request, string $strategy): bool
    {
        $config = config("rate-limiting.strategies.{$strategy}");
        $key = $this->buildKey($request, $strategy);

        return !$this->limiter->tooManyAttempts(
            $key,
            $config['requests'],
            $config['window'] / 60
        );
    }

    public function getRemaining(Request $request, string $strategy): int
    {
        $config = config("rate-limiting.strategies.{$strategy}");
        $key = $this->buildKey($request, $strategy);

        return max(0, $config['requests'] - $this->limiter->attempts($key));
    }

    private function buildKey(Request $request, string $strategy): string
    {
        return "rl:{$strategy}:" . (auth()->id() ?? $request->ip());
    }
}
