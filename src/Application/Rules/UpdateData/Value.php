<?php

namespace Lukaskolista\Gift\Application\Rules\UpdateData;

use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;

final readonly class Value implements Data
{
    public function __construct(private mixed $value) {}

    public function ifGiven(callable $callback): SuccessOrFailure
    {
        return $callback($this->value);
    }
}
