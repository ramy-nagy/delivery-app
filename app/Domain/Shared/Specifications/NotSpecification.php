<?php

namespace App\Domain\Shared\Specifications;

use Illuminate\Database\Eloquent\Builder;

class NotSpecification extends Specification
{
    public function __construct(private readonly Specification $inner) {}

    public function toSql(Builder $query): Builder
    {
        return $query->whereNot(function (Builder $q): void {
            $this->inner->toSql($q);
        });
    }
}
