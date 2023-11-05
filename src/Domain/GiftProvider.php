<?php

namespace Lukaskolista\Gift\Domain;

interface GiftProvider
{
    public function isGiftWithItemAndRuleExists(string $itemId, string $ruleId): bool;
}
