<?php

namespace Lukaskolista\Gift\Domain\Rule\Specification;

use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Rule\Specification;

final readonly class HasNotOtherGift implements Specification
{
    public function isSatisfiedBy(Container $container): bool
    {
        return !$container->hasGifts();
    }
}
