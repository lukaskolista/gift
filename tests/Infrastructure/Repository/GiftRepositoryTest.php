<?php

namespace Lukaskolista\Gift\Tests\Infrastructure\Repository;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\Gift;
use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\SpecificVersion;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\GiftRepository;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GiftRepositoryTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function saveNew(): array
    {
        $ruleId = self::$faker->uuid();
        $itemId = self::$faker->uuid();
        $itemQuantity = self::$faker->numberBetween();

        $gift = new Gift(self::$faker->uuid(), new Item($itemId, Quantity::of($itemQuantity)), $ruleId, null);
        $expectedData = ['item' => ['id' => $itemId, 'quantity' => $itemQuantity], 'ruleId' => $ruleId];

        $insertResult = $this->createMock(InsertOneResult::class);
        $insertResult
            ->method('getInsertedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('insertOne')
            ->with(['_id' => $gift->getId(), ...$expectedData, '_version' => 1])
            ->willReturn($insertResult);

        $repository = new GiftRepository($collection, new GenericAggregateRepository());
        $repository->save($gift, new NewAggergate());

        return [$gift, $expectedData];
    }

    #[Test]
    #[Depends('saveNew')]
    public function saveExisting(array $data): void
    {
        /**
         * @var Gift $gift
         * @var array $expectedData
         */
        [$gift, $expectedData] = $data;

        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult
            ->method('getModifiedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('updateOne')
            ->with(
                ['_id' => $gift->getId(), '_version' => 1],
                ['$set' => [...$expectedData, '_version' => 2]]
            )
            ->willReturn($updateResult);

        $repository = new GiftRepository($collection, new GenericAggregateRepository());
        $repository->save($gift, new SpecificVersion(1));

        self::assertEquals(2, $gift->getVersion());
    }
}
