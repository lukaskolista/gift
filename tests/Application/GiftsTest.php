<?php

namespace Lukaskolista\Gift\Tests\Application;

use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\AnyVersion;
use PHPUnit\Framework\Attributes\Test;

class GiftsTest extends ApplicationTestCase
{
    #[Test]
    public function changeItemQuantitySucceed(): void
    {
        $ruleId = self::$faker->uuid();
        $itemQuantity = self::$faker->numberBetween();
        $giftId = self::$faker->uuid();
        $containerId = self::$faker->uuid();

        $this->rules->create($ruleId, new Always());
        $this->gifts->create($giftId, self::$faker->uuid(), Quantity::of(self::$faker->numberBetween()), $ruleId);

        $this->gifts
            ->changeItemQuantity($giftId, Quantity::of($itemQuantity), new AnyVersion())
            ->on(failure: fn() => self::fail('Gift item quantity changing failed'));

        $this->containers->create($containerId);
        $availableGifts = $this->gifts->getAvailable($containerId)->on(
            success: fn(array $availableGifts) => $availableGifts,
            failure: fn() => self::fail('Can\'t get available gifts')
        );

        self::assertCount(1, $availableGifts);
        self::assertEquals($giftId, $availableGifts[0]->getId());
        self::assertEquals($itemQuantity, $availableGifts[0]->getItem()->getQuantity());
    }

    #[Test]
    public function getAvailableGiftsSucceed(): void
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

        $availableGifts = $this->gifts
            ->getAvailable($containerId)
            ->on(
                success: fn($gifts) => $gifts,
                failure: fn() => self::fail('Available gifts getting failed')
            );

        self::assertCount(2, $availableGifts);
    }
}
