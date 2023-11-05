<?php

namespace Lukaskolista\Gift\Application;

use Lukaskolista\Gift\Application\ReadModel\Rule\Search\Result\Pagination;
use Lukaskolista\Gift\Application\Rules\Error\DomainRuleViolation;
use Lukaskolista\Gift\Application\Rules\Error\IncompatibleVersion;
use Lukaskolista\Gift\Application\Rules\UpdateData;
use Lukaskolista\Gift\Domain\GiftRepository;
use Lukaskolista\Gift\Domain\Rule;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use Lukaskolista\Gift\Domain\RuleManager;
use Lukaskolista\Gift\Domain\RuleRepository;
use Lukaskolista\Gift\Domain\Violation;
use Lukaskolista\Gift\Framework\OptimisticLock\Invoker;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\NewAggergate;
use Lukaskolista\Gift\Framework\OptimisticLock\SaveFailed;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final readonly class Rules
{
    public function __construct(
        private Invoker $invoker,
        private RuleRepository $ruleRepository,
        private GiftRepository $giftRepository,
        private RuleManager $ruleManager
    ) {}

    public function create(string $ruleId, Specification $specification): SuccessOrFailure
    {
        return Rule::new($ruleId, $specification)
            ->on(
                success: function (Rule $rule) {
                    $this->ruleRepository->save($rule, new NewAggergate());

                    return new Success($this->ruleToReadModel($rule));
                },
                failure: fn() => new Failure(new DomainRuleViolation())
            );
    }

    public function update(string $ruleId, UpdateData $data, RequiredVersion $version): SuccessOrFailure
    {
        try {
            return $this->invoker->invoke(
                function () use ($ruleId, $data, $version) {
                    $rule = $this->ruleRepository->find($ruleId);

                    if ($rule === null) {
                        return new Failure();
                    }

                    $results = [
                        $data->specification->ifGiven(fn($specification) => $rule->changeSpecification($specification)),
                        $data->active->ifGiven(fn($active) => $active ? $rule->activate() : $rule->deactivate())
                    ];

                    foreach ($results as $result) {
                        if (!$result->toBool()) {
                            return new Failure(new DomainRuleViolation());
                        }
                    }

                    $this->ruleRepository->save($rule, $version);

                    return new Success($this->ruleToReadModel($rule));
                }
            );
        } catch (SaveFailed) {
            return new Failure(new IncompatibleVersion());
        }
    }

    public function delete(string $ruleId, RequiredVersion $version): SuccessOrFailure
    {
        return $this->invoker->invoke(
            function () use ($ruleId, $version) {
                $rule = $this->ruleRepository->find($ruleId);

                return $this->ruleManager
                    ->delete($rule, $version)
                    ->on(
                        success: fn() => new Success(),
                        failure: fn(Violation $violation) => new Failure(new DomainRuleViolation($violation))
                    );
            }
        );
    }

    public function search(int $page, int $limit): SuccessOrFailure
    {
        [$rules, $totalRules] = $this->ruleRepository->search($page, $limit);

        return new Success(
            new ReadModel\Rule\Search\Result(
                array_map(
                    fn(Rule $rule) => $this->ruleToReadModel($rule),
                    $rules
                ),
                new Pagination($page, ceil($totalRules / $limit), $limit)
            ),
        );
    }

    public function get(string $ruleId): SuccessOrFailure
    {
        $rule = $this->ruleRepository->find($ruleId);

        return $rule !== null
            ? new Success($this->ruleToReadModel($rule))
            : new Failure();
    }

    private function ruleToReadModel(Rule $rule): ReadModel\Rule
    {
        return new ReadModel\Rule(
            $rule->getId(),
            $this->specificationToReadModel($rule->getSpecification()),
            $rule->getVersion()
        );
    }

    private function specificationToReadModel(Specification $specification): object
    {
        return match ($specification::class) {
            AndCondition::class => new ReadModel\Rule\Specification\AndCondition(
                array_map(
                    fn(Specification $specification) => $this->specificationToReadModel($specification),
                    $specification->getSpecifications()
                )
            ),
            CartValue::class => new ReadModel\Rule\Specification\CartValue(
                match ($specification->getMoneyComparison()::class) {
                    MoreThanOrEqual::class => new ReadModel\Rule\Specification\MoneyComparison\MoreThanOrEqual(
                        $specification->getMoneyComparison()->getValue()
                    )
                }
            ),
            HasNotOtherGift::class => new ReadModel\Rule\Specification\HasNotOtherGift(),
            Always::class => new ReadModel\Rule\Specification\Always(),
        };
    }
}
