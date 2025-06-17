<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\HttpClient\Body\JsonBody;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\BodyInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\Body\NoBody;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

use function in_array;

/**
 * JSON HTTP-запрос.
 *
 * @implements HttpRequestInterface<JsonResponse>
 */
final readonly class JsonRequest implements HttpRequestInterface
{
    private bool $isPost;

    /**
     * @param non-empty-string $url
     * @param array<string|int>|null $query
     * @param SensitiveParameterValue<non-empty-string> $accessToken
     */
    public function __construct(
        private string $url,
        private ?array $query,
        private HttpMethod $method,
        private ?object $body,
        private SensitiveParameterValue $accessToken
    ) {
        $this->isPost = in_array($this->method, [HttpMethod::Post, HttpMethod::Put, HttpMethod::Patch], true);
    }

    public function getBody(): BodyInterface
    {
        return $this->body !== null
            ? new JsonBody($this->body)
            : new NoBody();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return [
            'Accept: application/json; charset=utf-8',
            'Authorization: ' . $this->accessToken->getValue(),
        ];
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
            $url .= '?' . http_build_query($this->query);
        }

        return $url;
    }

    public function isFollowLocation(): ?bool
    {
        return false;
    }

    public function isPost(): bool
    {
        return $this->isPost;
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
