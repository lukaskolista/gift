<?php

namespace Lukaskolista\Gift\Tests\Domain;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\AvailableGiftsProvider;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\RuleRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function collectAvailableSucceed(): void
    {
        $giftId = self::$faker->uuid();

        $availableGiftsProvider = $this->createMock(AvailableGiftsProvider::class);
        $availableGiftsProvider
            ->method('isAvailable')
            ->willReturn(true);

        $container = Container::new(self::$faker->uuid());

        $result = $container->collect(
            $giftId,
            $availableGiftsProvider,
            $this->createStub(GiftRepository::class),
            $this->createStub(RuleRepository::class)
        );

        self::assertTrue($result->toBool());
    }

    #[Test]
    public function collectUnavailableFailed(): void
    {
        $giftId = self::$faker->uuid();

        $availableGiftsProvider = $this->createMock(AvailableGiftsProvider::class);
        $availableGiftsProvider
            ->method('isAvailable')
            ->willReturn(false);

        $container = Container::new(self::$faker->uuid());

        $result = $container->collect(
            $giftId,
            $availableGiftsProvider,
            $this->createStub(GiftRepository::class),
            $this->createStub(RuleRepository::class)
        );

        self::assertFalse($result->toBool());
    }

    #[Test]
    public function removeCollectedGiftSucceed(): void
    {
        $giftId = self::$faker->uuid();

        $availableGiftsProvider = $this->createMock(AvailableGiftsProvider::class);
        $availableGiftsProvider
            ->method('isAvailable')
            ->willReturn(true);

        $container = Container::new(self::$faker->uuid());
        $container->collect(
            $giftId,
            $availableGiftsProvider,
            $this->createStub(GiftRepository::class),
            $this->createStub(RuleRepository::class)
        );

        $result = $container->remove($giftId);

        self::assertTrue($result->toBool());
        self::assertCount(0, $container->getGifts());
    }

    #[Test]
    public function removeNotCollectedGiftFailed(): void
    {
        $container = Container::new(self::$faker->uuid());
        $result = $container->remove(self::$faker->uuid());

        self::assertFalse($result->toBool());
    }
}
