<?php

namespace App\Domain\Shared\ValueObjects;

use Stringable;

class Money implements Stringable
{
    public function __construct(private readonly int $amountCents) {}

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function add(self $other): self
    {
        return new self($this->amountCents + $other->amountCents);
    }

    public function subtract(self $other): self
    {
        return new self($this->amountCents - $other->amountCents);
    }

    public function lessThan(self $other): bool
    {
        return $this->amountCents < $other->amountCents;
    }

    public function cents(): int
    {
        return $this->amountCents;
    }

    public function toFloat(): float
    {
        return $this->amountCents / 100;
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, '.', '');
    }
}
