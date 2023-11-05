<?php

namespace Lukaskolista\Gift\Application\ReadModel\Rule\Specification;

final readonly class AndCondition
{
    public function __construct(public array $specifications) {}
}
