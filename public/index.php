<?php

require_once __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$request = Request::createFromGlobals();
$routes = include __DIR__.'/../config/routes.php';

$context = new Routing\RequestContext();
$context->fromRequest($request);
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);

$containerBuilder = new ContainerBuilder();
$loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__.'/../config'));
$loader->load('services.php');
$containerBuilder->compile();

try {
    $route = $matcher->match($request->getPathInfo());
    $httpFoundationFactory = new HttpFoundationFactory();

    $psr17Factory = new HttpFactory();
    $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    $psrRequest = $psrHttpFactory->createRequest($request);

    $arguments = $route;
    unset($arguments['_route'], $arguments['_controller'], $arguments['_action']);

    $psrResponse = call_user_func(
        [$containerBuilder->get($route['_controller']), $route['_action']],
        $psrRequest,
        ...$arguments
    );

    $response = $httpFoundationFactory->createResponse($psrResponse);
} catch (ResourceNotFoundException $e) {
    $response = new Response(
        json_encode(['message' => 'Not Found']),
        404,
        ['Content-Type' => 'application-json']
    );
} catch (Throwable $e) {
    var_dump($e->__toString());exit;
    $response = new Response(
        json_encode(['message' => 'An error occurred']),
        500,
        ['Content-Type' => 'application-json']
    );
}

$response->send();

exit(0);
