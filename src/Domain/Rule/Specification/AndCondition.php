<?php

namespace Lukaskolista\Gift\Domain\Rule\Specification;

use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Rule\Specification;

final readonly class AndCondition implements Specification
{
    private array $specifications;

    public function __construct(Specification ...$specifications)
    {
        $this->specifications = $specifications;
    }

    public function isSatisfiedBy(Container $container): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($container)) {
                return false;
            }
        }

        return true;
    }

    public function getSpecifications(): array
    {
        return $this->specifications;
    }
}
