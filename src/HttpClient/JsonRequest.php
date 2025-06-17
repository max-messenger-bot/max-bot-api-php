<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\Body\JsonBody;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

use function in_array;

/**
 * @implements HttpRequestInterface<JsonResponse>
 */
final readonly class JsonRequest implements HttpRequestInterface
{
    public JsonBody|null $body;

    /**
     * @param non-empty-string $url
     * @param array<string|int|list<string|int>>|null $query
     * @param SensitiveParameterValue<non-empty-string> $accessToken
     */
    public function __construct(
        private string $url,
        private ?array $query,
        private HttpMethod $method,
        ?array $body,
        private SensitiveParameterValue $accessToken
    ) {
        $this->body = $body !== null
            ? new JsonBody($body, 'application/json; charset=utf-8')
            : null;
    }

    public function getBody(): ?string
    {
        return $this->body?->getBody();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $headers = [
            'Accept: application/json; charset=utf-8',
            'Authorization: ' . $this->accessToken->getValue(),
        ];

        if ($this->body !== null && $this->isPost()) {
            $headers[] = 'Content-Type: ' . $this->body->getBodyContentType();
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
        return $this->method->value;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        $url = $this->url;

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($this->query) {
            if (str_contains($this->url, '?')) {
                $url .= '&' . http_build_query($this->query);
            } else {
                $url .= '?' . http_build_query($this->query);
            }
        }

        return $url;
    }

    public function isFollowLocation(): ?bool
    {
        return false;
    }

    public function isPost(): bool
    {
        return in_array($this->method, [HttpMethod::Post, HttpMethod::Put, HttpMethod::Patch], true);
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
    ): JsonResponse {
        return new JsonResponse(
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
