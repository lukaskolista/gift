<?php

namespace Lukaskolista\Gift\Framework\Result;

abstract readonly class SuccessOrFailure
{
    abstract public function on(?callable $success = null, ?callable $failure = null): mixed;

    abstract public function toBool(): bool;
}
