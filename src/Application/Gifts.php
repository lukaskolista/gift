<?php

namespace Lukaskolista\Gift\Application;

use Lukaskolista\Gift\Domain\AvailableGiftsProvider;
use Lukaskolista\Gift\Domain\ContainerRepository;
use Lukaskolista\Gift\Domain\Gift;
use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\GiftProvider;
use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Domain\RuleRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\Invoker;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final readonly class Gifts
{
    public function __construct(
        private Invoker $invoker,
        private AvailableGiftsProvider $availableGiftsProvider,
        private GiftRepository $giftRepository,
        private RuleRepository $ruleRepository,
        private ContainerRepository $containerRepository,
        private GiftProvider $giftProvider
    ) {}

    public function create(string $giftId, string $itemId, Quantity $quantity, string $ruleId): SuccessOrFailure
    {
        $rule = $this->ruleRepository->find($ruleId);

        if ($rule === null) {
            return new Failure();
        }

        if ($this->giftProvider->isGiftWithItemAndRuleExists($itemId, $ruleId)) {
            return new Failure();
        }

        return Gift::new($giftId, new Item($itemId, $quantity), $ruleId)
            ->on(
                success: function (Gift $gift) {
                    $this->giftRepository->save($gift, new NewAggergate());

                    return new Success();
                },
                failure: fn() => new Failure()
            );
    }

    public function changeItemQuantity(string $giftId, Quantity $quantity, RequiredVersion $version): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($giftId, $quantity, $version) {
                $gift = $this->giftRepository->find($giftId);

                return $gift
                    ->changeItemQuantity($quantity)
                    ->on(
                        success: function () use ($gift, $version) {
                            $this->giftRepository->save($gift, $version);

                            return new Success();
                        },
                        failure: fn() => new Failure()
                    );
            }
        );
    }

    /**
     * @return Gift[]
     */
    public function getAvailable(string $containerId): SuccessOrFailure
    {
        $container = $this->containerRepository->find($containerId);

        if ($container === null) {
            return new Failure();
        }

        $availableGifts = $this->availableGiftsProvider->provideAvailableGifts(
            $container,
            $this->giftRepository,
            $this->ruleRepository
        );

        return new Success(
            array_map(
                fn(Gift $gift) => new ReadModel\Gift(
                    $gift->getId(),
                    new ReadModel\Gift\Item(
                        $gift->getItem()->getId(),
                        $gift->getItem()->getQuantity()->getValue()
                    )
                ),
                $availableGifts
            )
        );
    }
}
