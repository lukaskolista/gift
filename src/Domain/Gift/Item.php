<?php

namespace Lukaskolista\Gift\Domain\Gift;

use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final class Item
{
    public function __construct(private readonly string $id, private Quantity $quantity) {}

    public function changeQuantity(Quantity $quantity): SuccessOrFailure
    {
        $this->quantity = $quantity;

        return new Success();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }
}
