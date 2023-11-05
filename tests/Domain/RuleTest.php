<?php

namespace Lukaskolista\Gift\Tests\Domain;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function isSatisfiedWhenSpecificationMatches(): void
    {
        $ruleId = self::$faker->uuid();
        $rule = new Rule(
            $ruleId,
            new AndCondition(
                new CartValue(new MoreThanOrEqual(100)),
                new HasNotOtherGift()
            ),
            null
        );

        $isSatisfiedBy = $rule->isSatisfiedBy(new Container(self::$faker->uuid(), 100, [], null));

        self::assertTrue($isSatisfiedBy);
    }
}
