<?php

namespace App\Domain\Shared\Specifications;

use Illuminate\Database\Eloquent\Builder;

class OrSpecification extends Specification
{
    public function __construct(
        private readonly Specification $left,
        private readonly Specification $right,
    ) {}

    public function toSql(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where(function (Builder $q1): void {
                $this->left->toSql($q1);
            })->orWhere(function (Builder $q2): void {
                $this->right->toSql($q2);
            });
        });
    }
}
