<?php

namespace Lukaskolista\Gift\Domain\Rule\Specification;

use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison;
use Lukaskolista\Gift\Domain\Rule\Specification;

final readonly class CartValue implements Specification
{
    public function __construct(private MoneyComparison $moneyComparison) {}

    public function isSatisfiedBy(Container $container): bool
    {
        return $this->moneyComparison->compare($container->getTotalValue());
    }

    public function getMoneyComparison(): MoneyComparison
    {
        return $this->moneyComparison;
    }
}
