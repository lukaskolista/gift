<?php

namespace Lukaskolista\Gift\Application\ReadModel\Rule\Specification;

use Lukaskolista\Gift\Application\ReadModel\Rule\Specification\MoneyComparison\MoreThanOrEqual;

final readonly class CartValue
{
    public function __construct(public MoreThanOrEqual $moneyComparison) {}
}
