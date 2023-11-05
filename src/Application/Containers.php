<?php

namespace Lukaskolista\Gift\Application;

use Lukaskolista\Gift\Domain\AvailableGiftsProvider;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\ContainerRepository;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\RuleRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\Invoker;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final readonly class Containers
{
    public function __construct(
        private Invoker $invoker,
        private AvailableGiftsProvider $availableGiftsProvider,
        private GiftRepository $giftRepository,
        private RuleRepository $ruleRepository,
        private ContainerRepository $containerRepository
    ) {}

    public function create(string $containerId): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($containerId) {
                $container = Container::new($containerId);
                $this->containerRepository->save($container, new NewAggergate());

                return new Success();
            }
        );
    }

    public function changeTotalValue(string $containerId, int $value, RequiredVersion $version): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($containerId, $value, $version) {
                $container = $this->containerRepository->find($containerId);

                return $container
                    ->changeTotalValue($value)
                    ->on(
                        success: function (Container $container) use ($version) {
                            $this->containerRepository->save($container, $version);

                            return new Success();
                        },
                        failure: fn() => new Failure()
                    );
            }
        );
    }

    public function collect(string $containerId, string $giftId, RequiredVersion $version): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($containerId, $giftId, $version) {
                $container = $this->containerRepository->find($containerId);

                return $container
                    ->collect(
                        $giftId,
                        $this->availableGiftsProvider,
                        $this->giftRepository,
                        $this->ruleRepository
                    )
                    ->on(
                        success: function (Container $container) use ($version) {
                            $this->containerRepository->save($container, $version);

                            return new Success();
                        },
                        failure: fn() => new Failure()
                    );
            }
        );
    }

    public function remove(string $containerId, string $giftId, RequiredVersion $version): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($containerId, $giftId, $version) {
                $container = $this->containerRepository->find($containerId);

                return $container
                    ->remove($giftId)
                    ->on(
                        success: function () use ($container, $version) {
                            $this->containerRepository->save($container, $version);

                            return new Success();
                        },
                        failure: fn() => new Failure()
                    );
            }
        );
    }

    public function getGifts(string $containerId): SuccessOrFailure
    {
        $container = $this->containerRepository->find($containerId);

        if ($container === null) {
            return new Failure();
        }

        return new Success($container->getGifts());
    }
}
