<?php

namespace Lukaskolista\Gift\Application\ReadModel\Rule\Specification\MoneyComparison;

final readonly class MoreThanOrEqual
{
    public function __construct(public int $value) {}
}
