<?php

namespace Lukaskolista\Gift\Adapter\API\Public\Controller;

use Lukaskolista\Gift\Application\ReadModel;
use Lukaskolista\Gift\Application\Rules;
use Lukaskolista\Gift\Application\Rules\Error\DomainRuleViolation;
use Lukaskolista\Gift\Application\Rules\Error\IncompatibleVersion;
use Lukaskolista\Gift\Application\Rules\UpdateData;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison;
use Lukaskolista\Gift\Domain\Rule\MoneyComparison\MoreThanOrEqual;
use Lukaskolista\Gift\Domain\Rule\Specification;
use Lukaskolista\Gift\Domain\Rule\Specification\Always;
use Lukaskolista\Gift\Domain\Rule\Specification\AndCondition;
use Lukaskolista\Gift\Domain\Rule\Specification\CartValue;
use Lukaskolista\Gift\Domain\Rule\Specification\HasNotOtherGift;
use Lukaskolista\Gift\Framework\Api\ControllerTrait;
use Lukaskolista\Gift\Framework\Api\Error;
use Lukaskolista\Gift\Framework\Api\ErrorResponse;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Failure;
use Lukaskolista\Gift\Framework\Result\SuccessOrFailure\Success;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Uid\Uuid;

final readonly class RuleController
{
    use ControllerTrait;

    public function __construct(private Rules $rules) {}

    public function details(ServerRequestInterface $request, string $ruleId): ResponseInterface
    {
        return $this->rules
            ->get($ruleId)
            ->on(
                success: fn(ReadModel\Rule $rule) => $this->success(body: $this->ruleToOutput($rule)),
                failure: fn() => $this->notFound()
            );
    }

    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        $limit = max(min((int) ($request->getQueryParams()['limit'] ?? 20), 20), 1);

        return $this->rules
            ->search($page, $limit)
            ->on(
                success: fn(ReadModel\Rule\Search\Result $result) => $this->success([
                    'items' => array_map(
                        fn(ReadModel\Rule $rule) => $this->ruleToOutput($rule),
                        $result->rules
                    ),
                    'pagination' => [
                        'currentPage' => $result->pagination->currentPage,
                        'totalPages' => $result->pagination->totalPages,
                        'limit' => $result->pagination->limit,
                    ]
                ]),
                failure: fn() => $this->badRequest()
            );
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        if (!array_key_exists('specification', $data)) {
            return $this->badRequest(
                new ErrorResponse([
                    new Error(['specification'], ['Missing specification data'])
                ])
            );
        }

        return $this
            ->inputToSpecification($data['specification'], ['specification'])
            ->on(
                success: fn(Specification $specification) => $this->rules
                    ->create(Uuid::v4(), $specification)
                    ->on(
                        success: fn(ReadModel\Rule $rule) => $this->success(body: $this->ruleToOutput($rule)),
                        failure: fn(Rules\Error $error) => $this->handleError($error)
                    ),
                failure: fn(array $errors) => $this->badRequest(new ErrorResponse($errors))
            );
    }

    public function update(ServerRequestInterface $request, string $ruleId): ResponseInterface
    {
        $data = $request->getParsedBody();
        $updateData = new UpdateData();
        $errors = [];

        if (array_key_exists('specification', $data)) {
            $this
                ->inputToSpecification($data['specification'], ['specification'])
                ->on(
                    success: function (Specification $specification) use ($updateData) {
                        $updateData->specification($specification);
                    },
                    failure: function (array $specificationErrors) use (&$errors) {
                        $errors = [...$errors, ...$specificationErrors];
                    }
                );
        }

        if (array_key_exists('active', $data)) {
            if (is_bool($data['active'])) {
                $updateData->active($data['active']);
            } else {
                $errors = [...$errors, new Error(['active'], ['Value must be boolean'])];
            }
        }

        if (count($errors) > 0) {
            return $this->badRequest(new ErrorResponse($errors));
        }

        return $this->rules
            ->update($ruleId, $updateData, $this->versionHeader($request, 'Rule-Version'))
            ->on(
                success: fn(ReadModel\Rule $rule) => $this->success(body: $this->ruleToOutput($rule)),
                failure: fn(Rules\Error $error) => $this->handleError($error)
            );
    }

    public function delete(ServerRequestInterface $request, string $ruleId): ResponseInterface
    {
        return $this->rules
            ->delete($ruleId, $this->versionHeader($request, 'Rule-Version'))
            ->on(
                success: fn() => $this->noContent(),
                failure: fn(Error $error) => match ($error::class) {
                    IncompatibleVersion::class => $this->conflict(
                        new ErrorResponse([
                            new Error([], ['Incompatible rule version'])
                        ])
                    ),
                    DomainRuleViolation::class => $this->conflict(),
                    default => $this->badRequest(
                        new ErrorResponse([
                            new Error([], ['Something went wrong'])
                        ])
                    ) // TODO Transalate error from application
                }
            );
    }

    private function handleError(Rules\Error $error): ResponseInterface
    {
        return match ($error::class) {
            IncompatibleVersion::class => $this->conflict(
                new ErrorResponse([
                    new Error([], ['Incompatible rule version'])
                ])
            ),
            DomainRuleViolation::class => $this->badRequest(
                new ErrorResponse([
                    new Error([], ['Domain rule violation'])
                ])
            ),
            default => $this->badRequest(
                new ErrorResponse([
                    new Error([], ['Something went wrong'])
                ])
            ) // TODO Transalate error from application
        };
    }

    private function inputToSpecification(array $specification, array $path): SuccessOrFailure
    {
        return match ($this->getKey($specification)) {
            'and' => (function () use ($specification, $path) {
                if (!is_array($specification['and'])) {
                    return new Failure([
                        new Error([...$path, 'and'], ['Invalid conditions'])
                    ]);
                }

                if (count($specification['and']) < 2) {
                    return new Failure([
                        new Error([...$path, 'and'], ['At least 2 conditions required'])
                    ]);
                }

                $conditions = [];
                foreach ($specification['and'] as $key => $condition) {
                    $conditions[] = $this
                        ->inputToSpecification($condition, [...$path, 'and', $key])
                        ->on(
                            success: fn(Specification $specification) => $specification,
                            failure: fn(array $errors) => $errors
                        );
                }

                $errors = array_values(
                    array_filter(
                        $conditions,
                        fn($condition) => !($condition instanceof Specification)
                    )
                );

                return count($errors) === 0
                    ? new Success(new AndCondition(...$conditions))
                    : new Failure(array_merge(...$errors));
            })(),
            'cartValue' => (function () use ($specification, $path) {
                $moneyComparison = match ($this->getKey($specification['cartValue'])) {
                    'moreThanOrEqual' => (function () use ($path, $specification) {
                        $moreThanOrEqual = $specification['cartValue']['moreThanOrEqual'];

                        if (!is_int($moreThanOrEqual)) {
                            return new Failure(
                                new Error(
                                    [...$path, 'cartValue', 'moreThanOrEqual'],
                                    ['Comparison value must be integer']
                                )
                            );
                        }

                        return new Success(new MoreThanOrEqual($specification['cartValue']['moreThanOrEqual']));
                    })(),
                    default => new Failure(
                        new Error([...$path, 'cartValue'], ['Invalid money comparison'])
                    )
                };

                return $moneyComparison->on(
                    success: fn(MoneyComparison $moneyComparison) => new Success(new CartValue($moneyComparison)),
                    failure: fn(Error $error) => new Failure([$error])
                );
            })(),
            'hasNotOtherGift' => match ($this->getKey($specification['always'])) {
                null => new Success(new HasNotOtherGift()),
                default => new Failure([
                    new Error([...$path, 'always'], ['Pass null as hasNotOtherGift specification value'])
                ])
            },
            'always' => match ($this->getKey($specification['always'])) {
                null => new Success(new Always()),
                default => new Failure([
                    new Error([...$path, 'always'], ['Pass null as always specification value'])
                ])
            },
            default => new Failure(
                [new Error($path, ['Invalid specification type'])]
            )
        };
    }

    private function getKey(?array $data): ?string
    {
        return array_keys($data ?? [])[0] ?? null;
    }

    private function ruleToOutput(ReadModel\Rule $rule): array
    {
        return [
            'id' => $rule->id,
            'specification' => $this->specificationToOutput($rule->specification),
            '_version' => $rule->version
        ];
    }

    private function specificationToOutput(object $specification): array
    {
        return match ($specification::class) {
            ReadModel\Rule\Specification\AndCondition::class => [
                'and' => array_map(
                    fn(object $specification) => $this->specificationToOutput($specification),
                    $specification->specifications
                )
            ],
            ReadModel\Rule\Specification\CartValue::class => [
                'cartValue' => match ($specification->moneyComparison::class) {
                    ReadModel\Rule\Specification\MoneyComparison\MoreThanOrEqual::class => [
                        'moreThanOrEqual' => $specification->moneyComparison->value
                    ]
                }
            ],
            ReadModel\Rule\Specification\HasNotOtherGift::class => [
                'hasNotOtherGift' => null
            ],
            ReadModel\Rule\Specification\Always::class => [
                'always' => null
            ]
        };
    }
}
