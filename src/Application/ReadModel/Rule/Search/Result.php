<?php

namespace Lukaskolista\Gift\Application\ReadModel\Rule\Search;

use Lukaskolista\Gift\Application\ReadModel\Rule;
use Lukaskolista\Gift\Application\ReadModel\Rule\Search\Result\Pagination;

final readonly class Result
{
    public function __construct(
        /** @var Rule[] */ public array $rules,
        public Pagination $pagination
    ) {}
}
