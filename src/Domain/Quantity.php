<?php

namespace Lukaskolista\Gift\Domain;

final readonly class Quantity
{
    public function __construct(private string $value) {}

    /**
     * Use this named constructor instead of native in your code.
     * Native constructor is only for infrastructure purposes.
     */
    public static function of(string $value): self
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a number',
                $value
            ));
        }

        return new self($value);
    }

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
