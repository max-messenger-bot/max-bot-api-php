<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;

use function strtolower;

/**
 * HTTP-ответ.
 */
final readonly class HttpResponse implements HttpResponseInterface
{
    /**
     * @param int $httpCode Код ответа HTTP.
     * @param non-empty-string $url Адрес запроса, на который получен ответ.
     * @param string|null $contentType Значение заголовка `Content-Type` ответа.
     * @param string $body Тело ответа.
     * @param string[][] $headers Заголовки ответа: имя заголовка и список его значений.
     */
    public function __construct(
        private int $httpCode,
        private string $url,
        private ?string $contentType = null,
        private string $body = '',
        private array $headers = [],
    ) {}

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getFirstHeader(string $name): ?string
    {
        $name = strtolower($name);

        foreach ($this->headers as $header => $values) {
            if (strtolower((string) $header) === $name) {
                return $values[0] ?? null;
            }
        }

        return null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
