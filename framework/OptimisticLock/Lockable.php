<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock;

interface Lockable
{
    public function getId(): string;

    public function getVersion(): ?int;

    public function incrementVersion(): void;
}
