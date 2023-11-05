<?php

namespace Lukaskolista\Gift\Application\ReadModel;

use Lukaskolista\Gift\Application\ReadModel\Gift\Item;

final readonly class Gift
{
    public function __construct(private string $id, private Item $item) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
