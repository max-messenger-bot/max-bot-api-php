<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Upload;

use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;

/**
 * HTTP-запрос для получения информации о загружаемом файле.
 *
 * @implements HttpRequestInterface<ResumableInfoResponse>
 */
final readonly class ResumableInfoRequest implements HttpRequestInterface
{
    /**
     * @param non-empty-string $url
     */
    public function __construct(
        private string $url
    ) {
    }

    public function getBody(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMaxRedirects(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return HttpMethod::Get->value;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function isFollowLocation(): ?bool
    {
        return false;
    }

    public function isPost(): bool
    {
        return false;
    }

    public function isResponseHeadersRequired(): ?bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function makeResponse(
        int $httpCode,
        string $url,
        string $effectiveUrl,
        ?string $redirectUrl,
        array $headers,
        ?string $contentType,
        string $response
    ): ResumableInfoResponse {
        return new ResumableInfoResponse(
            $this,
            $httpCode,
            $url,
            $effectiveUrl,
            $redirectUrl,
            $contentType,
            $response
        );
    }
}
