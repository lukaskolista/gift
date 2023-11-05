<?php

namespace Lukaskolista\Gift\Tests\Domain;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\AvailableGiftsProvider;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Gift;
use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use Lukaskolista\Gift\Domain\RuleRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AvailableGiftsProviderTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function provideAvailableGifts(): void
    {
        $ruleIds = [self::$faker->uuid(), self::$faker->uuid()];
        $gifts = [
            new Gift(self::$faker->uuid(), new Item(self::$faker->uuid(), Quantity::of(1)), $ruleIds[0], null),
            new Gift(self::$faker->uuid(), new Item(self::$faker->uuid(), Quantity::of(1)), $ruleIds[1], null),
            new Gift(self::$faker->uuid(), new Item(self::$faker->uuid(), Quantity::of(1)), $ruleIds[0], null),
        ];

        $giftRepository = $this->createMock(GiftRepository::class);
        $giftRepository
            ->method('findByRules')
            ->with([$ruleIds[0]])
            ->willReturn([$gifts[0], $gifts[2]]);

        $rules = [
            new Rule($ruleIds[0], new CartValue(new MoreThanOrEqual(100)), null),
            new Rule($ruleIds[1], new HasNotOtherGift(), null),
        ];

        $ruleRepository = $this->createMock(RuleRepository::class);
        $ruleRepository
            ->method('findAll')
            ->willReturn($rules);

        $availableGiftsProvider = new AvailableGiftsProvider();
        $availableGifts = $availableGiftsProvider->provideAvailableGifts(
            new Container(
                self::$faker->uuid(),
                100,
                [
                    $gifts[0]
                ],
                null
            ),
            $giftRepository,
            $ruleRepository
        );

        self::assertCount(2, $availableGifts);
    }
}
