<?php

namespace App\Domain\Orders\Repositories;

use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Specifications\Specification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function find(int $id): ?Order;

    public function save(Order $order): Order;

    /**
     * @return Collection<int, Order>
     */
    public function matching(Specification $spec): Collection;

    public function paginate(Specification $spec, int $perPage = 15): LengthAwarePaginator;

    public function count(Specification $spec): int;
}
