<?php

namespace Lukaskolista\Gift\Application\Rules;

use Lukaskolista\Gift\Application\Rules\UpdateData\Data;
use Lukaskolista\Gift\Application\Rules\UpdateData\Undefined;
use Lukaskolista\Gift\Application\Rules\UpdateData\Value;
use Lukaskolista\Gift\Domain\Rule\Specification;

final class UpdateData
{
    public function __construct(
        public Data $specification = new Undefined(),
        public Data $active = new Undefined()
    ) {}

    public function specification(Specification $specification): void
    {
        $this->specification = new Value($specification);
    }

    public function active(bool $active): void
    {
        $this->active = new Value($active);
    }
}
