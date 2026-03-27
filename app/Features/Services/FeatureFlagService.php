<?php
namespace App\Features\Services;

use App\Features\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class FeatureFlagService
{
    public function isEnabled(string $name, ?User $user = null): bool
    {
        $flag = $this->getFlag($name);
        return $flag?->isEnabledFor($user) ?? false;
    }

    private function getFlag(string $name): ?FeatureFlag
    {
        return Cache::remember(
            "feature_flag:{$name}",
            3600,
            fn() => FeatureFlag::where('name', $name)->first()
        );
    }

    public function invalidateCache(string $name): void
    {
        Cache::forget("feature_flag:{$name}");
    }
}
