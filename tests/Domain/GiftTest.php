<?php

namespace Lukaskolista\Gift\Tests\Domain;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Gift;
use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GiftTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function changeItemQuantitySucceed(): void
    {
        $gift = new Gift(
            self::$faker->uuid(),
            new Item(self::$faker->uuid(), Quantity::of(1)),
            self::$faker->uuid(),
            null
        );
        $gift->changeItemQuantity(Quantity::of(2));

        self::assertTrue($gift->getItem()->getQuantity()->isEqualTo(Quantity::of(2)));
    }
}
