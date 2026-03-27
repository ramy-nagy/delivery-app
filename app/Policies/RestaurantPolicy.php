<?php

namespace App\Policies;

use App\Models\Restaurant;

class RestaurantPolicy extends BaseCrudPolicy
{
    protected function permissionPrefix(): string
    {
        return 'restaurant';
    }

    // In this app we authorize only based on permissions (no per-record checks yet).
    public function viewAny(\App\Models\User $user): bool
    {
        return parent::viewAny($user);
    }

    public function view(\App\Models\User $user, Restaurant $model): bool
    {
        return parent::view($user, $model);
    }

    public function create(\App\Models\User $user): bool
    {
        return parent::create($user);
    }

    public function update(\App\Models\User $user, Restaurant $model): bool
    {
        return parent::update($user, $model);
    }

    public function delete(\App\Models\User $user, Restaurant $model): bool
    {
        return parent::delete($user, $model);
    }

    public function restore(\App\Models\User $user, Restaurant $model): bool
    {
        return parent::restore($user, $model);
    }
}

