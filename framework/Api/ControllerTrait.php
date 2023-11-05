<?php

namespace Lukaskolista\Gift\Framework\Api;

use GuzzleHttp\Psr7\Response;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\AnyVersion;
use Lukaskolista\Gift\Framework\OptimisticLock\RequiredVersion\SpecificVersion;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait ControllerTrait
{
    private function versionHeader(ServerRequestInterface $request, string $name): RequiredVersion
    {
        if (
            $request->hasHeader($name)
            && (int) $request->getHeaderLine($name) == $request->getHeaderLine($name)
        ) {
            return new SpecificVersion($request->getHeaderLine($name));
        }

        return new AnyVersion();
    }

    private function success(mixed $body = null): ResponseInterface
    {
        return new Response(status: 200, body: $body !== null ? json_encode($body) : null);
    }

    private function noContent(): ResponseInterface
    {
        return new Response(status: 204);
    }

    private function badRequest(\JsonSerializable $body = null): ResponseInterface
    {
        return $this->jsonResponse(400, $body);
    }

    private function conflict(\JsonSerializable $body = null): ResponseInterface
    {
        return $this->jsonResponse(409, $body);
    }

    private function jsonResponse(int $status, \JsonSerializable $body = null): ResponseInterface
    {
        return (new Response(status: $status, body: $body !== null ? json_encode($body) : null))
            ->withHeader('Content-Type', 'application-json');
    }

    private function notFound(): ResponseInterface
    {
        return new Response(404);
    }
}