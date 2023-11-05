<?php

namespace Lukaskolista\Gift\Tests\Application;

use Faker\Factory;
use Faker\Generator;
use Lukaskolista\Gift\Application\Containers;
use Lukaskolista\Gift\Application\Gifts;
use Lukaskolista\Gift\Application\Rules;
use Lukaskolista\Gift\Domain\AvailableGiftsProvider;
use Lukaskolista\Gift\Domain\GiftProvider;
use Lukaskolista\Gift\Framework\OptimisticLock\Invoker;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Normalizer\Rule\SpecificationNormalizer;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\ContainerRepository;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\GiftRepository;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\RuleRepository;
use MongoDB\Client as MongoDBClient;
use PHPUnit\Framework\TestCase;

abstract class ApplicationTestCase extends TestCase
{
    protected static Generator $faker;
    protected Rules $rules;
    protected Gifts $gifts;
    protected Containers $containers;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    protected function setUp(): void
    {
        $mongoClient = new MongoDBClient('mongodb://root:root@127.0.0.1:'.$_ENV['MONGO_PORT']);
        $mongoDatabase = $mongoClient->selectDatabase('giftTest');

        $giftCollection = $mongoDatabase->selectCollection('gift');
        $giftCollection->drop();

        $ruleCollection = $mongoDatabase->selectCollection('rule');
        $ruleCollection->drop();

        $containerCollection = $mongoDatabase->selectCollection('container');
        $containerCollection->drop();

        $genericAggregateRepository = new GenericAggregateRepository();
        $availableGiftsProvider = new AvailableGiftsProvider();
        $giftRepository = new GiftRepository(
            $giftCollection,
            $genericAggregateRepository
        );
        $ruleRepository = new RuleRepository(
            $ruleCollection,
            new SpecificationNormalizer(),
            $genericAggregateRepository
        );
        $containerRepository = new ContainerRepository(
            $containerCollection,
            $genericAggregateRepository
        );
        $invoker = new Invoker(0);

        $this->rules = new Rules(new Invoker(0), $ruleRepository, $giftRepository);

        $this->gifts = new Gifts(
            $invoker,
            $availableGiftsProvider,
            $giftRepository,
            $ruleRepository,
            $containerRepository,
            $this->createStub(GiftProvider::class)
        );

        $this->containers = new Containers(
            $invoker,
            $availableGiftsProvider,
            $giftRepository,
            $ruleRepository,
            new ContainerRepository(
                $mongoDatabase->selectCollection('container'),
                $genericAggregateRepository
            )
        );
    }
}
