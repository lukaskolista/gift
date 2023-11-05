<?php

namespace Lukaskolista\Gift\Framework\Api;

final readonly class Error
{
    public function __construct(public array $path, public array $messages) {}
}
