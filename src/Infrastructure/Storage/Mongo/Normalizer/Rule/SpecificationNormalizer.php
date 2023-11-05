<?php

namespace Lukaskolista\Gift\Infrastructure\Storage\Mongo\Normalizer\Rule;

use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;

final class SpecificationNormalizer
{
    public function normalize(Specification $specification): array
    {
        return match ($specification::class) {
            AndCondition::class => [
                'and' => array_map(
                    fn(Specification $specification) => $this->normalize($specification),
                    $specification->getSpecifications()
                )
            ],
            CartValue::class => [
                'cartValue' => [
                    'moneyComparison' => match ($specification->getMoneyComparison()::class) {
                        MoreThanOrEqual::class => [
                            'moreThanOrEqual' => $specification->getMoneyComparison()->getValue()
                        ]
                    }
                ]
            ],
            HasNotOtherGift::class => [
                'hasNotOtherGift' => null
            ],
            Always::class => [
                'always' => null
            ]
        };
    }

    public function denormalize(object $data): Specification
    {
        return match ($this->getKey($data)) {
            'and' => new AndCondition(
                ...array_map(
                    fn(object $specification) => $this->denormalize($specification),
                    (array) $data->and
                )
            ),
            'cartValue' => new CartValue(
                match ($this->getKey($data->cartValue->moneyComparison)) {
                    'moreThanOrEqual' => new MoreThanOrEqual($data->cartValue->moneyComparison->moreThanOrEqual)
                }
            ),
            'hasNotOtherGift' => new HasNotOtherGift(),
            'always' => new Always()
        };
    }

    private function getKey(object $data): string
    {
        return array_keys(get_object_vars($data->jsonSerialize()))[0]
            ?? throw new \InvalidArgumentException('No specification type key');
    }
}
