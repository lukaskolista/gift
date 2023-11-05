<?php

namespace Lukaskolista\Gift\Application\ReadModel\Gift;

final readonly class Item
{
    public function __construct(private string $id, private float $quantity) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }
}
