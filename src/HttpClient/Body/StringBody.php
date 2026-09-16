<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Body;

use MaxMessenger\Bot\Contract\Http\BodyInterface;

/**
 * Тело запроса, заданное строкой.
 */
final readonly class StringBody implements BodyInterface
{
    /**
     * @param string $body Содержимое тела запроса.
     * @param non-empty-string|null $contentType Значение заголовка `Content-Type`.
     */
    public function __construct(
        private string $body,
        private ?string $contentType = null,
    ) {}

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
