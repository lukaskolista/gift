<?php

namespace Lukaskolista\Gift\Framework\Result\SuccessOrFailure;

use Lukaskolista\Gift\Framework\Result\Nothing;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;

final readonly class Success extends SuccessOrFailure
{
    private array $arguments;

    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    public function on(?callable $success = null, ?callable $failure = null): mixed
    {
        return $success !== null ? $success(...$this->arguments) : new Nothing();
    }

    public function toBool(): bool
    {
        return true;
    }
}
