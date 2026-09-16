<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contract\Http\BodyInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;

/**
 * HTTP-запрос.
 */
final readonly class HttpRequest implements HttpRequestInterface
{
    /**
     * @param non-empty-string $method Метод запроса, например `GET` или `POST`.
     * @param non-empty-string $url Адрес запроса вместе со строкой параметров.
     * @param array<non-empty-string, non-empty-string> $headers Заголовки запроса: имя заголовка и его значение.
     * @param BodyInterface|null $body Тело запроса или `null`, если тела нет.
     * @param non-negative-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     */
    public function __construct(
        private string $method,
        private string $url,
        private array $headers = [],
        private ?BodyInterface $body = null,
        private ?int $timeout = null,
    ) {}

    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
