<?php

namespace Lukaskolista\Gift\Domain;

use Lukaskolista\Gift\Domain\Rule\Specification;
use Lukaskolista\Gift\Framework\OptimisticLock\Lockable;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;

final class Rule implements Lockable
{
    use Versionable;

    public function __construct(
        private readonly string $id,
        private Specification $specification,
        ?int $version
    ) {
        $this->version = $version;
    }

    public static function new(string $id, Specification $specification): SuccessOrFailure
    {
        return new Success(new self($id, $specification, null));
    }

    public function changeSpecification(Specification $specification): SuccessOrFailure
    {
        $this->specification = $specification;

        return new Success();
    }

    public function activate(): SuccessOrFailure
    {

    }

    public function deactivate(): SuccessOrFailure
    {

    }

    public function is(string $id): bool
    {
        return $this->id === $id;
    }

    public function isSatisfiedBy(Container $container): bool
    {
        return $this->specification->isSatisfiedBy($container);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSpecification(): Specification
    {
        return $this->specification;
    }
}
