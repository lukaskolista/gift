<?php

namespace Lukaskolista\Gift\Domain\Rule\MoneyComparison;

use Lukaskolista\Gift\Domain\Rule\MoneyComparison;

final readonly class MoreThanOrEqual implements MoneyComparison
{
    public function __construct(private int $value) {}

    public function compare(int $money): bool
    {
        return $money >= $this->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
