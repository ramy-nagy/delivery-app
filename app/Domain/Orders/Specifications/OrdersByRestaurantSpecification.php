<?php
namespace App\Domain\Orders\Specifications;

use App\Domain\Shared\Specifications\Specification;
use Illuminate\Database\Eloquent\Builder;

class OrdersByRestaurantSpecification extends Specification
{
    public function __construct(private int $restaurantId) {}

    public function toSql(Builder $query): Builder
    {
        return $query->where('restaurant_id', $this->restaurantId);
    }
}
