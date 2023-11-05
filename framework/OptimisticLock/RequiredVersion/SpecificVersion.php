<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

final readonly class SpecificVersion implements RequiredVersion
{
    public function __construct(private int $version) {}

    public function isComatibleWith(?int $version): bool
    {
        return $this->version === $version;
    }
}
