<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lukaskolista\Gift\Adapter\API\Public\Controller\RuleController;
use Lukaskolista\Gift\Application\Rules;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\RuleManager;
use Lukaskolista\Gift\Domain\RuleRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\Invoker;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Normalizer\Rule\SpecificationNormalizer;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\GiftRepository as MongoGiftRepository;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository\RuleRepository as MongoRuleRepository;
use MongoDB\Client;
use MongoDB\Collection;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->autowire();

    $services->set(Invoker::class)->arg('$defaultMaxRetries', 10);
    $services->set(SpecificationNormalizer::class);
    $services->set(GenericAggregateRepository::class);

    $services
        ->set(Client::class)
        ->lazy()
        ->arg('$uri', 'mongodb://root:root@127.0.0.1:27022');

    $services
        ->set('gift.mongo.collection.rule', Collection::class)
        ->factory([service(Client::class), 'selectCollection'])
        ->arg('$databaseName', 'gift')
        ->arg('$collectionName', 'rule');

    $services
        ->set(RuleRepository::class, MongoRuleRepository::class)
        ->arg('$collection', service('gift.mongo.collection.rule'));

    $services
        ->set('gift.mongo.collection.gift', Collection::class)
        ->factory([service(Client::class), 'selectCollection'])
        ->arg('$databaseName', 'gift')
        ->arg('$collectionName', 'gift');

    $services
        ->set(GiftRepository::class, MongoGiftRepository::class)
        ->arg('$collection', service('gift.mongo.collection.gift'));

    $services->set(Rules::class);
    $services->set(RuleManager::class);

    $services
        ->set(RuleController::class)
        ->public()
        ->arg('$rules', service(Rules::class));
};
