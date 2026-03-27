<?php
namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use App\Models\User;

class FeatureFlag extends Model
{
    protected $fillable = [
        'name',
        'description',
        'enabled',
        'rollout_percentage',
        'targeting_rules',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'targeting_rules' => AsCollection::class,
    ];

    public function isEnabledFor(?User $user = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $user = $user ?? auth()->user();

        if (!$user) {
            return $this->rollout_percentage === 100;
        }

        // Check rollout percentage
        if ($this->rollout_percentage < 100) {
            $hash = crc32($user->id . $this->name);
            if (($hash % 100) >= $this->rollout_percentage) {
                return false;
            }
        }

        return $this->matchesTargetingRules($user);
    }

    private function matchesTargetingRules(User $user): bool
    {
        if ($this->targeting_rules->isEmpty()) {
            return true;
        }

        foreach ($this->targeting_rules as $rule) {
            if (!$this->evaluateRule($rule, $user)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateRule(array $rule, User $user): bool
    {
        return match ($rule['type'] ?? null) {
            'user_ids' => in_array($user->id, $rule['values'] ?? []),
            'user_emails' => in_array($user->email, $rule['values'] ?? []),
            'roles' => $user->roles()->whereIn('name', $rule['values'] ?? [])->exists(),
            default => true,
        };
    }
}
