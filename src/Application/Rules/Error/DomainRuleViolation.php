<?php

namespace Lukaskolista\Gift\Application\Rules\Error;

use Lukaskolista\Gift\Application\Rules\Error;
use Lukaskolista\Gift\Domain\Violation;

final readonly class DomainRuleViolation implements Error
{
    public function __construct(public Violation $violation) {}
}
