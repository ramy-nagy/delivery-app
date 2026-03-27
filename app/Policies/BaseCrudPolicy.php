<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BaseCrudPolicy
{
    /**
     * Permission prefix, e.g. "restaurant" => "restaurant.viewAny", "restaurant.update", etc.
     */
    abstract protected function permissionPrefix(): string;

    protected function perm(string $ability): string
    {
        return $this->permissionPrefix() . '.' . $ability;
    }

    public function viewAny(User $user): bool
    {
        return $user->can($this->perm('viewAny'));
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->perm('view'));
    }

    public function create(User $user): bool
    {
        return $user->can($this->perm('create'));
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->perm('update'));
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->perm('delete'));
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can($this->perm('restore'));
    }
}

