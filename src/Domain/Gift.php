<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Framework\OptimisticLock\Lockable;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final class Gift implements Lockable
{
    use Versionable;

    public function __construct(
        private readonly string $id,
        private readonly Item $item,
        private readonly string $ruleId,
        ?int $version
    ) {
        $this->version = $version;
    }

    public static function new(string $id, Item $item, string $ruleId): SuccessOrFailure
    {
        return new Success(new self($id, $item, $ruleId, null));
    }

    public function changeItemQuantity(Quantity $quantity): SuccessOrFailure
    {
        return $this->item->changeQuantity($quantity);
    }

    public function is(string $id): bool
    {
        return $this->id === $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getRuleId(): string
    {
        return $this->ruleId;
    }
}
