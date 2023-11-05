<?php

namespace Lukaskolista\Gift\Application\ReadModel\Rule\Search\Result;

final readonly class Pagination
{
    public function __construct(public int $currentPage, public int $totalPages, public int $limit) {}
}
