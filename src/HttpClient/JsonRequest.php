<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\HttpClient\Body\JsonBody;
use MaxMessenger\Bot\HttpClient\Exceptions\AccessTokenException;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\BodyInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use Mj4444\SimpleHttpClient\HttpRequest\Body\NoBody;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

use function in_array;
use function is_string;

/**
 * JSON HTTP-запрос.
 *
 * @implements HttpRequestInterface<JsonResponse>
 * @internal
 */
final readonly class JsonRequest implements HttpRequestInterface
{
    private bool $isPost;

    /**
     * @param non-empty-string $url
     * @param array<string|int>|null $query
     * @param positive-int|null $timeout
     * @param SensitiveParameterValue<non-empty-string> $accessToken
     */
    public function __construct(
        private string $url,
        private ?array $query,
        private HttpMethod $method,
        private ?object $body,
        private ?int $timeout,
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

    public function getConnectTimeout(): null
    {
        return null;
    }

    public function getHeaders(): array
    {
        $accessToken = $this->accessToken->getValue();
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($accessToken) || empty($accessToken)) {
            throw new AccessTokenException();
        }

        return [
            'Accept: application/json; charset=utf-8',
            'Authorization: ' . $accessToken,
        ];
    }

    public function getMaxRedirects(): null
    {
        return null;
    }

    public function getMethod(): string
    {
        return $this->method->value;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

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
            $contentType,
            $response
        );
    }
}
