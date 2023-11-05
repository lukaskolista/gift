<?php

namespace Lukaskolista\Gift\Tests\Infrastructure\Repository;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\SpecificVersion;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\ContainerRepository;
use MongoDB\Collection;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContainerRepositoryTest extends TestCase
{
    private static Generator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    #[Test]
    public function saveNew(): array
    {
        $giftIds = [self::$faker->uuid(), self::$faker->uuid()];
        $container = new Container(self::$faker->uuid(), 123, $giftIds, null);

        $expectedData = ['totalValue' => 123, 'gifts' => $giftIds];

        $insertResult = $this->createMock(InsertOneResult::class);
        $insertResult
            ->method('getInsertedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('insertOne')
            ->with(['_id' => $container->getId(), ...$expectedData, '_version' => 1])
            ->willReturn($insertResult);

        $repository = new ContainerRepository($collection, new GenericAggregateRepository());
        $repository->save($container, new NewAggergate());

        return [$container, $expectedData];
    }

    #[Test]
    #[Depends('saveNew')]
    public function saveExisting(array $data): void
    {
        /**
         * @var Container $container
         * @var array $expectedData
         */
        [$container, $expectedData] = $data;

        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult
            ->method('getModifiedCount')
            ->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(self::once())
            ->method('updateOne')
            ->with(
                ['_id' => $container->getId(), '_version' => 1],
                ['$set' => [...$expectedData, '_version' => 2]]
            )
            ->willReturn($updateResult);

        $repository = new ContainerRepository($collection, new GenericAggregateRepository());
        $repository->save($container, new SpecificVersion(1));

        self::assertEquals(2, $container->getVersion());
    }
}
