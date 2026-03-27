<?php

namespace App\Repositories\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CrudRepository
{
    /**
     * @param class-string<Model> $modelClass
     */
    public function __construct(private readonly string $modelClass)
    {
    }

    /**
     * @return Builder<Model>
     */
    public function baseQuery(bool $onlyTrashed = false): Builder
    {
        /** @var Builder<Model> $query */
        $query = $this->modelClass::query();

        if ($onlyTrashed) {
            return $query->onlyTrashed();
        }

        return $query;
    }

    /**
     * @return Model|null
     */
    public function find(int $id, bool $withTrashed = false): ?Model
    {
        $query = $this->modelClass::query();

        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        /** @var Model|null $model */
        $model = $query->find($id);

        return $model;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        /** @var Model $model */
        $model = $this->modelClass::create($data);

        return $model;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Model
    {
        $model = $this->find($id, true);

        if ($model === null) {
            throw new \RuntimeException('Model not found');
        }

        $model->fill($data);
        $model->save();

        return $model;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    public function restore(Model $model): void
    {
        if (method_exists($model, 'restore')) {
            $model->restore();
        }
    }
}

