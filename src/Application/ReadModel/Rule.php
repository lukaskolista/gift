<?php

namespace Lukaskolista\Gift\Application\ReadModel;

use Lukaskolista\Gift\Application\ReadModel\Rule\Specification\Always;
use Lukaskolista\Gift\Application\ReadModel\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Application\ReadModel\Rule\Specification\CartValue;
use Lukaskolista\Gift\Application\ReadModel\Rule\Specification\HasNotOtherGift;

final readonly class Rule
{
    public function __construct(
        public string $id,
        public Always|AndCondition|CartValue|HasNotOtherGift $specification,
        public int $version
    ) {}
}
