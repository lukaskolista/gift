<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Domain\Error\Rule\ThereAreGiftsWithTheRule;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final readonly class RuleManager
{
    public function __construct(private GiftRepository $giftRepository, private RuleRepository $ruleRepository) {}

    public function delete(Rule $rule, RequiredVersion $version): SuccessOrFailure
    {
        if ($this->giftRepository->isWithRuleExisting($rule->getId())) {
            return new Failure(new ThereAreGiftsWithTheRule());
        }

        $this->ruleRepository->delete($rule, $version);

        return new Success();
    }
}
