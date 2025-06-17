<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Upload;

use MaxMessenger\Bot\Contracts\Uploaders\StreamInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\BodyInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\Body\StreamBody;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;

use function sprintf;

/**
 * HTTP-запрос для возобновляемой загрузки файла.
 *
 * @implements HttpRequestInterface<ResumableUploadResponse>
 */
final readonly class ResumableUploadRequest implements HttpRequestInterface
{
    private int $expectedHttpCode;
    private int $size;

    /**
     * @param non-empty-string $url
     * @param StreamInterface $stream
     * @param non-negative-int $offset
     * @param non-negative-int $length
     * @param bool $isJsonResponse
     */
    public function __construct(
        private string $url,
        private StreamInterface $stream,
        private int $offset,
        private int $length,
        private bool $isJsonResponse
    ) {
        $this->size = $this->stream->getSize();
        $this->expectedHttpCode = $this->offset + $this->length >= $this->size ? 200 : 201;
    }

    public function getBody(): BodyInterface
    {
        return new StreamBody($this->stream->getResource(), 'application/octet-stream', $this->offset, $this->length);
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $fileName = rawurlencode($this->stream->getPostName());
        $contentDisposition = sprintf('attachment; filename=%s', $fileName);
        $range = sprintf('%d-%d/%d', $this->offset, $this->offset + $this->length - 1, $this->size);

        $headers = [
            'Content-Disposition: ' . $contentDisposition,
            'Content-Length: ' . $this->length,
            'Content-Range: bytes ' . $range,
        ];

        if ($this->isJsonResponse) {
            $headers[] = 'Accept: application/json; charset=utf-8';
        }

        return $headers;
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
    ): ResumableUploadResponse {
        return new ResumableUploadResponse(
            $this,
            $httpCode,
            $url,
            $effectiveUrl,
            $redirectUrl,
            $contentType,
            $response,
            $this->expectedHttpCode
        );
    }
}
