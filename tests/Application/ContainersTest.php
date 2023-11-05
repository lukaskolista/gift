<?php

namespace Lukaskolista\Gift\Tests\Application;

use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\AnyVersion;
use PHPUnit\Framework\Attributes\Test;

class ContainersTest extends ApplicationTestCase
{
    #[Test]
    public function collectAvailableGiftsSucceed(): void
    {
        $containerId = self::$faker->uuid();
        $ruleIds = [self::$faker->uuid(), self::$faker->uuid()];
        $itemIds = [self::$faker->uuid(), self::$faker->uuid()];
        $giftIds = [self::$faker->uuid(), self::$faker->uuid()];

        $this->rules->create($ruleIds[0], new Always())->on(failure: fn() => self::fail('Rule #1 creation failed'));
        $this->rules->create($ruleIds[1], new Always())->on(failure: fn() => self::fail('Rule #2 creation failed'));
        $this->gifts->create($giftIds[0], $itemIds[0], Quantity::of(1), $ruleIds[0])->on(failure: fn() => self::fail('Gift #1 creation failed'));
        $this->gifts->create($giftIds[1], $itemIds[1], Quantity::of(1), $ruleIds[1])->on(failure: fn() => self::fail('Gift #2 creation failed'));
        $this->containers->create($containerId)->on(failure: fn() => self::fail('Container creating failed'));
        $this->containers->collect($containerId, $giftIds[0], new AnyVersion())->on(failure: fn() => self::fail('Gift #1 collecting failed'));

        $this->containers->collect($containerId, $giftIds[1], new AnyVersion())->on(failure: fn() => self::fail('Gift #2 collecting failed'));
        $gifts = $this->containers->getGifts($containerId)->on(
            success: fn(array $gifts) => $gifts,
            failure: fn() => self::fail('Gifts getting failed')
        );

        self::assertEquals($giftIds, $gifts);
    }

    #[Test]
    public function removeCollectedGiftSucceed(): void
    {
        $containerId = self::$faker->uuid();
        $ruleId = self::$faker->uuid();
        $itemIds = [self::$faker->uuid(), self::$faker->uuid()];
        $giftIds = [self::$faker->uuid(), self::$faker->uuid()];

        $this->rules->create($ruleId, new Always())->on(failure: fn() => self::fail('Rule creation failed'));
        $this->gifts->create($giftIds[0], $itemIds[0], Quantity::of(1), $ruleId)->on(failure: fn() => self::fail('Gift #1 creation failed'));
        $this->gifts->create($giftIds[1], $itemIds[1], Quantity::of(1), $ruleId)->on(failure: fn() => self::fail('Gift #2 creation failed'));
        $this->containers->create($containerId)->on(failure: fn() => self::fail('Container creating failed'));
        $this->containers->collect($containerId, $giftIds[0], new AnyVersion())->on(failure: fn() => self::fail('Gift #1 collecting failed'));
        $this->containers->collect($containerId, $giftIds[1], new AnyVersion())->on(failure: fn() => self::fail('Gift #2 collecting failed'));

        $this->containers->remove($containerId, $giftIds[0], new AnyVersion())->on(failure: fn() => self::fail('Gift #1 removing failed'));
        $gifts = $this->containers->getGifts($containerId)->on(
            success: fn(array $gifts) => $gifts,
            failure: fn() => self::fail('Gifts getting failed')
        );

        self::assertEquals([$giftIds[1]], $gifts);
    }
}
