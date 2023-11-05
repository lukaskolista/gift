<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;

interface RuleRepository
{
    public function save(Rule $rule, RequiredVersion $version): void;

    public function delete(Rule $rule, RequiredVersion $version): void;

    public function find(string $id): ?Rule;

    /**
     * @return Rule[]
     */
    public function findAll(): array;

    public function search(int $page, int $limit): array;
}
