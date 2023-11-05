<?php

namespace Lukaskolista\Gift\Application\Rules\UpdateData;

use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;

interface Data
{
    public function ifGiven(callable $callback): SuccessOrFailure;
}
