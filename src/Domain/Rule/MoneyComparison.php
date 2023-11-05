<?php

namespace Lukaskolista\Gift\Domain\Rule;

interface MoneyComparison
{
    public function compare(int $money): bool;
}
