<?php
namespace App\Domain\Orders\Specifications;

use App\Domain\Shared\Specifications\Specification;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

class PendingOrdersSpecification extends Specification
{
    public function toSql(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING->value);
    }
}
