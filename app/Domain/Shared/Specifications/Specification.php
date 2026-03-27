<?php
namespace App\Domain\Shared\Specifications;

use Illuminate\Database\Eloquent\Builder;

abstract class Specification
{
    abstract public function toSql(Builder $query): Builder;

    public function and(Specification $spec): Specification
    {
        return new AndSpecification($this, $spec);
    }

    public function or(Specification $spec): Specification
    {
        return new OrSpecification($this, $spec);
    }
}
