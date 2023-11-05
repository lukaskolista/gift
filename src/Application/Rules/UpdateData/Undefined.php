<?php

namespace Lukaskolista\Gift\Application\Rules\UpdateData;

use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final readonly class Undefined implements Data
{
    public function ifGiven(callable $callback): SuccessOrFailure
    {
        return new Success();
    }
}
