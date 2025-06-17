<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Upload;

use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;

/**
 * HTTP-ответ для получения информации о загружаемом файле.
 *
 * @implements HttpResponseInterface<ResumableInfoRequest>
 */
final class ResumableInfoResponse implements HttpResponseInterface
{
    protected bool $contentTypeValidated = false;

    public function __construct(
        public readonly ResumableInfoRequest $request,
        public readonly int $httpCode,
        public readonly string $url,
        public readonly string $effectiveUrl,
        public readonly ?string $redirectUrl,
        public readonly ?string $contentType,
        public readonly string $body
    ) {
    }

    /**
     * @inheritDoc
     */
    public function checkContentType(string|array|null $expectedContentType = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function checkHttpCode(int|array $allowedCode = 200): void
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getData(): null
    {
        return null;
    }

    public function getEffectiveUrl(): string
    {
        return $this->effectiveUrl;
    }

    public function getFirstHeader(string $name): null
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

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): ResumableInfoRequest
    {
        return $this->request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
