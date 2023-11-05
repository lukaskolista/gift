<?php

namespace Lukaskolista\Gift\Framework\OptimisticLock\Mongo;

use Lukaskolista\Gift\Framework\OptimisticLock\DeleteFailed;
use Lukaskolista\Gift\Framework\OptimisticLock\IncompatibleVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\Lockable;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\SaveFailed;
use MongoDB\Collection;

final class GenericAggregateRepository
{
    public function save(Collection $collection, Lockable $aggregate, RequiredVersion $version, array $data): void
    {
        if (!$version->isComatibleWith($aggregate->getVersion())) {
            throw new SaveFailed();
        }

        if ($aggregate->getVersion() === null) {
            $result = $collection->insertOne([
                '_id' => $aggregate->getId(),
                ...$this->enrichData($aggregate, $data)
            ]);

            if ($result->getInsertedCount() !== 1) {
                throw new IncompatibleVersion();
            }
        } else {
            $result = $collection->updateOne(
                ['_id' => $aggregate->getId(), '_version' => $aggregate->getVersion()],
                ['$set' => $this->enrichData($aggregate, $data)]
            );

            if ($result->getModifiedCount() === 0) {
                throw new IncompatibleVersion();
            }
        }

        $aggregate->incrementVersion();
    }

    private function enrichData(Lockable $lockable, array $data): array
    {
        return $data + [
            '_version' => $lockable->getVersion() !== null
                ? $lockable->getVersion() + 1
                : 1
        ];
    }

    public function delete(Collection $collection, Lockable $aggregate, RequiredVersion $version): void
    {
        if (!$version->isComatibleWith($aggregate->getVersion())) {
            throw new DeleteFailed();
        }

        $result = $collection->deleteOne(['_id' => $aggregate->getId(), '_version' => $aggregate->getVersion()]);

        if ($result->getDeletedCount() === 0) {
            throw new IncompatibleVersion();
        }
    }
}
