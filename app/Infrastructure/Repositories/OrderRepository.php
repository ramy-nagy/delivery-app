<?php

namespace App\Infrastructure\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface as ContractsOrderRepositoryInterface;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Repositories\OrderRepositoryInterface;
use App\Domain\Shared\Specifications\Specification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface, ContractsOrderRepositoryInterface
{
    public function find(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function matching(Specification $spec): Collection
    {
        return $spec->toSql(Order::query())->get();
    }

    public function paginate(Specification $spec, int $perPage = 15): LengthAwarePaginator
    {
        return $spec->toSql(Order::query())->paginate($perPage);
    }

    public function count(Specification $spec): int
    {
        return $spec->toSql(Order::query())->count();
    }

    public function save(Order $order): Order
    {
        $order->save();

        return $order;
    }
}
