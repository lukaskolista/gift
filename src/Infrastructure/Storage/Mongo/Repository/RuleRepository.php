<?php

namespace Lukaskolista\Gift\Infrastructure\Storage\Mongo\Repository;

use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\RuleRepository as RuleRepositoryInterface;
use Lukaskolista\Gift\Framework\OptimisticLock\Mongo\GenericAggregateRepository;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Infrastructure\Storage\Mongo\Normalizer\Rule\SpecificationNormalizer;
use MongoDB\Collection;

readonly class RuleRepository implements RuleRepositoryInterface
{
    public function __construct(
        private Collection $collection,
        private SpecificationNormalizer $ruleSpecificationNormalizer,
        private GenericAggregateRepository $genericAggregateRepository
    ) {}

    public function save(Rule $rule, RequiredVersion $version): void
    {
        $this->genericAggregateRepository->save(
            $this->collection,
            $rule,
            $version,
            [
                'specification' => $this->ruleSpecificationNormalizer->normalize($rule->getSpecification())
            ]
        );
    }

    public function delete(Rule $rule, RequiredVersion $version): void
    {
        $this->genericAggregateRepository->delete($this->collection, $rule, $version);
    }

    public function find(string $id): ?Rule
    {
        $document = $this->collection->findOne(['_id' => $id]);

        return $document !== null
            ? $this->createRule($document)
            : null;
    }

    public function findAll(): array
    {
        $documents = $this->collection->find();

        return array_map(
            fn(object $document) => $this->createRule($document),
            $documents->toArray()
        );
    }

    public function search(int $page, int $limit): array
    {
        $documents = $this->collection->find([], ['skip' => $page * $limit - $limit, 'limit' => $limit]);

        return [
            array_map(
                fn(object $document) => $this->createRule($document),
                $documents->toArray()
            ),
            $this->collection->countDocuments()
        ];
    }

    private function createRule(object $document): Rule
    {
        return new Rule(
            $document->_id,
            $this->ruleSpecificationNormalizer->denormalize($document->specification),
            $document->_version
        );
    }
}
