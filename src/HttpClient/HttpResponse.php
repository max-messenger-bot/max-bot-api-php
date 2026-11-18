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
     * @param string|null $contentType Значение заголовка `Content-Type` ответа.
     * @param string $body Тело ответа.
     * @param string[][] $headers Заголовки ответа: имя заголовка и список его значений.
     */
    public function __construct(
        private int $httpCode,
        private ?string $contentType = null,
        private string $body = '',
        private array $headers = [],
    ) {}

    /**
     * @return string Тело ответа.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string|null Значение заголовка `Content-Type` ответа.
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @param string $name Имя заголовка без учёта регистра.
     * @return string|null Первое значение заголовка или `null`, если заголовка нет.
     */
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

    /**
     * @return string[][] Заголовки ответа: имя заголовка и список его значений.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return int Код ответа HTTP.
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
