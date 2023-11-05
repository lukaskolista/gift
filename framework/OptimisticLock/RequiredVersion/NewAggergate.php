<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

final class NewAggergate implements RequiredVersion
{
    public function isComatibleWith(?int $version): bool
    {
        return $version === null;
    }
}
