<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Upload;

use Mj4444\SimpleHttpClient\Contracts\HttpRequest\FileInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\StringFileInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\Body\MultipartFormBody;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;

/**
 * HTTP-запрос для простой загрузки файла.
 *
 * @implements HttpRequestInterface<SimpleUploadResponse>
 */
final readonly class SimpleUploadRequest implements HttpRequestInterface
{
    /**
     * @param non-empty-string $url
     */
    public function __construct(
        private string $url,
        private FileInterface|StringFileInterface $file,
        private bool $isJsonResponse
    ) {
    }

    public function getBody(): MultipartFormBody
    {
        return new MultipartFormBody(['data' => $this->file]);
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->isJsonResponse
            ? ['Accept: application/json; charset=utf-8']
            : [];
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
        return HttpMethod::Post->value;
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
        return true;
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
    ): SimpleUploadResponse {
        return new SimpleUploadResponse(
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
