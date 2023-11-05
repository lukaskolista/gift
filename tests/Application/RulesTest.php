<?php

namespace Lukaskolista\Gift\Tests\Application;

use Lukaskolista\Gift\Application\ReadModel\Rule;
use Lukaskolista\Gift\Application\ReadModel\Rule\Search\Result;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use PHPUnit\Framework\Attributes\Test;

class RulesTest extends ApplicationTestCase
{
    #[Test]
    public function createRuleSucceed(): void
    {
        $ruleId = self::$faker->uuid();

        $this->rules->create($ruleId, new Always());
    }

    #[Test]
    public function searchSucceed(): void
    {
        $ruleIds = [self::$faker->uuid(), self::$faker->uuid(), self::$faker->uuid(), self::$faker->uuid(), self::$faker->uuid()];
        $this->rules->create($ruleIds[0], new Always());
        $this->rules->create($ruleIds[1], new Always());
        $this->rules->create($ruleIds[2], new Always());
        $this->rules->create($ruleIds[3], new Always());
        $this->rules->create($ruleIds[4], new Always());

        $result = $this->rules->search(1, 2)->on(
            success: fn($rules) => $rules,
            failure: fn() => self::fail('Rule searching failed')
        );
        self::assertResult($result, [$ruleIds[0], $ruleIds[1]], 2, 1, 3, 2);

        $result = $this->rules->search(2, 2)->on(
            success: fn($rules) => $rules,
            failure: fn() => self::fail('Rule searching failed')
        );
        self::assertResult($result, [$ruleIds[2], $ruleIds[3]], 2, 2, 3, 2);

        $result = $this->rules->search(3, 2)->on(
            success: fn($rules) => $rules,
            failure: fn() => self::fail('Rule searching failed')
        );
        self::assertResult($result, [$ruleIds[4]], 1, 3, 3, 2);
    }

    private static function assertResult(
        Result $result,
        array $rules,
        int $count,
        int $currentPage,
        int $totalPages,
        int $limit
    ): void {
        self::assertCount($count, $result->rules);

        foreach ($rules as $i => $ruleId) {
            self::assertEquals($ruleId, $result->rules[$i]->id);
        }

        self::assertContainsOnlyInstancesOf(Rule::class, $result->rules);
        self::assertEquals($totalPages, $result->pagination->totalPages);
        self::assertEquals($currentPage, $result->pagination->currentPage);
        self::assertEquals($limit, $result->pagination->limit);
    }
}
