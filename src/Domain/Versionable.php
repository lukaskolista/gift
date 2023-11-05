<?php

namespace Lukaskolista\Gift\Domain;

trait Versionable
{
    private ?int $version;

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function incrementVersion(): void
    {
        $this->version !== null ? $this->version++ : $this->version = 1;
    }
}
