<?php

namespace Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository;

use Lukaskolista\Gift\Domain\Container;
use Lukaskolista\Gift\Domain\ContainerRepository as ContainerRepositoryInterface;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use MongoDB\Collection;

readonly class ContainerRepository implements ContainerRepositoryInterface
{
    public function __construct(
        private Collection $collection,
        private GenericAggregateRepository $genericAggregateRepository
    ) {}

    public function save(Container $container, RequiredVersion $version): void
    {
        $this->genericAggregateRepository->save(
            $this->collection,
            $container,
            $version,
            [
                'totalValue' => $container->getTotalValue(),
                'gifts' => $container->getGifts()
            ]
        );
    }

    public function find(string $id): ?Container
    {
        $document = $this->collection->findOne(['_id' => $id]);

        return $document !== null
            ? new Container($document->_id, $document->totalValue, (array) $document->gifts, $document->_version)
            : null;
    }
}
