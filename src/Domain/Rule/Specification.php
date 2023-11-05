<?php

namespace Lukaskolista\Gift\Domain\Rule;

use Lukaskolista\Gift\Domain\Container;

interface Specification
{
    public function isSatisfiedBy(Container $container): bool;
}
