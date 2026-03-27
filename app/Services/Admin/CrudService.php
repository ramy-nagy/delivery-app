<?php

namespace App\Services\Admin;

use App\Repositories\Admin\CrudRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class CrudService
{
    public function __construct(private readonly CrudRepository $repository)
    {
    }

    public function paginate(int $perPage, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = $this->repository->baseQuery($onlyTrashed);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->orderByDesc('id')->paginate($perPage);

        return $paginator;
    }

    public function find(int $id, bool $withTrashed = false): ?Model
    {
        return $this->repository->find($id, $withTrashed);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    public function delete(Model $model): void
    {
        $this->repository->delete($model);
    }

    public function restore(Model $model): void
    {
        $this->repository->restore($model);
    }
}

