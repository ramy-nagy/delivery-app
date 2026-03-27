<?php
namespace App\Domain\Shared\Specifications;

use Illuminate\Database\Eloquent\Builder;

class AndSpecification extends Specification
{
    public function __construct(
        private Specification $left,
        private Specification $right
    ) {}

    public function toSql(Builder $query): Builder
    {
        return $this->right->toSql($this->left->toSql($query));
    }
}
