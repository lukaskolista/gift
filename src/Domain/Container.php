<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Framework\OptimisticLock\Lockable;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final class Container implements Lockable
{
    use Versionable;

    public function __construct(
        private readonly string $id,
        private int $totalValue,
        private array $gifts,
        ?int $version
    ) {
        $this->version = $version;
    }

    public static function new(string $id): self
    {
        return new self($id, 0, [], null);
    }

    public function changeTotalValue(int $totalValue): SuccessOrFailure
    {
        $this->totalValue = $totalValue;

        return new Success($this);
    }

    public function collect(
        string $giftId,
        AvailableGiftsProvider $availableGiftsProvider,
        GiftRepository $giftRepository,
        RuleRepository $ruleRepository
    ): SuccessOrFailure {
        if (!$availableGiftsProvider->isAvailable($giftId, $this, $giftRepository, $ruleRepository)) {
            return new Failure();
        }

        $this->gifts[] = $giftId;

        return new Success($this);
    }

    public function remove(string $giftId): SuccessOrFailure
    {
        if (!in_array($giftId, $this->gifts)) {
            return new Failure();
        }

        unset($this->gifts[array_search($giftId, $this->gifts)]);

        return new Success();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTotalValue(): int
    {
        return $this->totalValue;
    }

    public function hasGifts(): bool
    {
        return count($this->gifts) > 0;
    }

    public function getGifts(): array
    {
        return array_values($this->gifts);
    }
}
