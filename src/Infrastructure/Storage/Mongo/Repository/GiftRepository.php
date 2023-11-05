<?php

namespace Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository;

use Lukaskolista\Gift\Domain\Gift;
use Lukaskolista\Gift\Domain\Gift\Item;
use Lukaskolista\Gift\Domain\GiftRepository as GiftRepositoryInterface;
use Lukaskolista\Gift\Domain\GiftProvider;
use Lukaskolista\Gift\Domain\Quantity;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use MongoDB\Collection;

readonly class GiftRepository implements GiftRepositoryInterface, GiftProvider
{
    public function __construct(
        private Collection $collection,
        private GenericAggregateRepository $genericAggregateRepository
    ) {}

    public function save(Gift $gift, RequiredVersion $version): void
    {
        $this->genericAggregateRepository->save(
            $this->collection,
            $gift,
            $version,
            [
                'item' => [
                    'id' => $gift->getItem()->getId(),
                    'quantity' => $gift->getItem()->getQuantity()->getValue()
                ],
                'ruleId' => $gift->getRuleId()
            ]
        );
    }

    public function find(string $id): ?Gift
    {
        $document = $this->collection->findOne(['_id' => $id]);

        return $document !== null ? $this->createGift($document) : null;
    }

    public function findAll(): iterable
    {
        $documents = $this->collection->find();

        foreach ($documents as $document) {
            yield $this->createGift($document);
        }
    }

    public function findByRules(array $ruleIds): array
    {
        $documents = $this->collection->find(['ruleId' => ['$in' => $ruleIds]]);

        return array_map(
            fn(object $document) => $this->createGift($document),
            $documents->toArray()
        );
    }

    private function createGift(object $document): Gift
    {
        return new Gift(
            $document->_id,
            new Item($document->item->id, new Quantity($document->item->quantity)),
            $document->ruleId,
            $document->_version
        );
    }

    public function isWithRuleExisting(string $ruleId): bool
    {
        $document = $this->collection->findOne(['ruleId' => $ruleId]);

        return $document !== null;
    }

    public function isGiftWithItemAndRuleExists(string $itemId, string $ruleId): bool
    {
        $document = $this->collection->findOne(['itemId' => $itemId, 'ruleId' => $ruleId]);

        return $document !== null;
    }
}
