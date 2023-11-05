<?php

namespace Lukaskolista\Gift\Tests\Infrastructure\Repository;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\SpecificVersion;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Normalizer\Rule\SpecificationNormalizer;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\RuleRepository;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleRepositoryTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function saveNew(): array
    {
        $rule = Rule::new(
            self::$faker->uuid(),
            new AndCondition(
                new CartValue(new MoreThanOrEqual(123)),
                new HasNotOtherGift(),
                new Always()
            )
        )->on(success: fn(Rule $rule) => $rule);

        $expectedData = [
            'specification' => [
                'and' => [
                    ['cartValue' => ['moneyComparison' => ['moreThanOrEqual' => 123]]],
                    ['hasNotOtherGift' => null],
                    ['always' => null]
                ]
            ]
        ];

        $insertResult = $this->createMock(InsertOneResult::class);
        $insertResult
            ->method('getInsertedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('insertOne')
            ->with([
                '_id' => $rule->getId(),
                ...$expectedData,
                '_version' => 1
            ])
            ->willReturn($insertResult);

        $repository = new RuleRepository(
            $collection,
            new SpecificationNormalizer(),
            new GenericAggregateRepository()
        );
        $repository->save($rule, new NewAggergate());

        return [$rule, $expectedData];
    }

    #[Test]
    #[Depends('saveNew')]
    public function saveExisting(array $data): void
    {
        /**
         * @var Rule $rule
         * @var array $expectedData
         */
        [$rule, $expectedData] = $data;

        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult
            ->method('getModifiedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('updateOne')
            ->with(
                ['_id' => $rule->getId(), '_version' => 1],
                [
                    '$set' => [
                        ...$expectedData,
                        '_version' => 2
                    ]
                ]
            )
            ->willReturn($updateResult);

        $repository = new RuleRepository(
            $collection,
            new SpecificationNormalizer(),
            new GenericAggregateRepository()
        );
        $repository->save($rule, new SpecificVersion(1));

        self::assertEquals(2, $rule->getVersion());
    }
}
