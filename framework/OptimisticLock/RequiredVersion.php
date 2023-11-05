<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock;

interface RequiredVersion
{
    public function isComatibleWith(?int $version): bool;
}
