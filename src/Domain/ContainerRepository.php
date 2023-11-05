<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

interface ContainerRepository
{
    public function save(Container $container, RequiredVersion $version): void;

    public function find(string $id): ?Container;
}
