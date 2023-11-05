<?php

namespace Lukaskolista\Gift\Domain;

class AvailableGiftsProvider
{
    /**
     * @return Gift[]
     */
    public function provideAvailableGifts(
        Container $container,
        GiftRepository $giftRepository,
        RuleRepository $ruleRepository
    ): array {
        $matchedRuleIds = [];

        foreach ($ruleRepository->findAll() as $rule) {
            if ($rule->isSatisfiedBy($container)) {
                $matchedRuleIds[] = $rule->getId();
            }
        }

        return $giftRepository->findByRules($matchedRuleIds);
    }

    public function isAvailable(
        string $giftId,
        Container $container,
        GiftRepository $giftRepository,
        RuleRepository $ruleRepository
    ): bool {
        foreach ($this->provideAvailableGifts($container, $giftRepository, $ruleRepository) as $gift) {
            if ($gift->is($giftId)) {
                return true;
            }
        }

        return false;
    }
}
