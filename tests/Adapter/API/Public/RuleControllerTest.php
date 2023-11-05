<?php

namespace Lukaskolista\Gift\Tests\Adapter\API\Public;

use Lukaskolista\Gift\Adapter\API\Public\Controller\RuleController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleControllerTest extends TestCase
{
    #[Test]
    public function createSucceed(): void
    {
        $controller = new RuleController();
        $controller->create();
    }
}
