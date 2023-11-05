<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock;

readonly class Invoker
{
    public function __construct(private int $defaultMaxRetries) {}

    public function invoke(callable $callback, int $maxRetries = null): mixed
    {
        $retries = 0;

        do {
            try {
                return $callback();
            } catch (IncompatibleVersion) {}
        } while (++$retries <= $maxRetries ?? $this->defaultMaxRetries);

        throw new SaveFailed();
    }
}
