<?php
namespace App\Domain\Shared\Services;

use Illuminate\Support\Facades\Cache;
use Closure;

class CacheService
{
    public function remember(
        string $key,
        Closure $callback,
        string $strategy = 'default',
        array $additionalTags = []
    ): mixed {
        $config = config("cache.strategies.{$strategy}", [
            'ttl' => 3600,
            'tags' => [],
        ]);

        $tags = array_merge($config['tags'], $additionalTags);

        if (!empty($tags)) {
            return Cache::tags($tags)->remember(
                $key,
                $config['ttl'],
                $callback
            );
        }

        return Cache::remember($key, $config['ttl'], $callback);
    }

    public function invalidate(string|array $tags): void
    {
        if (is_string($tags)) {
            Cache::tags([$tags])->flush();
        } else {
            Cache::tags($tags)->flush();
        }
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function put(string $key, mixed $value, int $ttl = null): void
    {
        Cache::put($key, $value, $ttl);
    }
}
