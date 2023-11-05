<?php

namespace Lukaskolista\Gift\Framework\Api;

final readonly class ErrorResponse implements \JsonSerializable
{
    public function __construct(private array $errors) {}

    public function jsonSerialize(): mixed
    {
        $errors = [];

        foreach ($this->errors as $error) {
            foreach ($error->messages as $message) {
                $key = implode('.', $error->path);

                if (!array_key_exists($key, $errors)) {
                    $errors[$key] = [
                        'path' => $error->path,
                        'messages' => []
                    ];
                }

                $errors[$key]['messages'] = [
                    ...($errors[$key]['messages'] ?? []),
                    $message
                ];
            }
        }

        return [
            'errors' => array_values($errors)
        ];
    }
}
