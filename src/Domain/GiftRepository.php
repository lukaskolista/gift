<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

interface GiftRepository
{
    public function save(Gift $gift, RequiredVersion $version): void;

    public function find(string $id): ?Gift;

    /**
     * @return Gift[]
     */
    public function findAll(): iterable;

    /**
     * @param string[] $ruleIds
     * @return Gift[]
     */
    public function findByRules(array $ruleIds): array;

    public function isWithRuleExisting(string $ruleId): bool;
}
