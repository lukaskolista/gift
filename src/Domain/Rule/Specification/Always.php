<?php

namespace Lukaskolista\Gift\Domain\Rule\Specification;

use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Rule\Specification;

final readonly class Always implements Specification
{
    public function isSatisfiedBy(Container $container): bool
    {
        return true;
    }
}
