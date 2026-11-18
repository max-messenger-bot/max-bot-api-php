<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use MaxMessenger\Bot\Contract\Http\BodyInterface;
use MaxMessenger\Bot\Contract\Http\StreamInterface;

/**
 * Тело запроса, заданное потоком.
 */
final readonly class FakeStreamBody implements BodyInterface
{
    /**
     * @param StreamInterface $stream Поток с содержимым тела запроса.
     * @param non-empty-string $contentType Значение заголовка `Content-Type`.
     */
    public function __construct(
        private StreamInterface $stream,
        private string $contentType,
    ) {}

    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
