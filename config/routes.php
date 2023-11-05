<?php

use Lukaskolista\Gift\Adapter\API\Public\Controller\RuleController;
use Symfony\Component\Routing;

$routes = new Routing\RouteCollection();
$routes->add(
    'rule_create',
    new Routing\Route(
        '/api/rule',
        [
            '_controller' => RuleController::class,
            '_action' => 'create'
        ],
        methods: ['POST']
    )
);
$routes->add(
    'rule_get',
    new Routing\Route(
        '/api/rule/{ruleId}',
        [
            '_controller' => RuleController::class,
            '_action' => 'details'
        ],
        methods: ['GET']
    )
);
$routes->add(
    'rule_search',
    new Routing\Route(
        '/api/rule',
        [
            '_controller' => RuleController::class,
            '_action' => 'search'
        ],
        methods: ['GET']
    )
);
$routes->add(
    'rule_update',
    new Routing\Route(
        '/api/rule/{ruleId}',
        [
            '_controller' => RuleController::class,
            '_action' => 'update'
        ],
        methods: ['PATCH']
    )
);
$routes->add(
    'rule_delete',
    new Routing\Route(
        '/api/rule/{ruleId}',
        [
            '_controller' => RuleController::class,
            '_action' => 'delete'
        ],
        methods: ['DELETE']
    )
);

return $routes;
