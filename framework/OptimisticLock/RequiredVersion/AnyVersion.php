<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

final class AnyVersion implements RequiredVersion
{
    public function isComatibleWith(?int $version): bool
    {
        return true;
    }
}
