<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Contracts\MaxHttpClientInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\CurlHttpClient;
use SensitiveParameter;
use SensitiveParameterValue;

final class MaxApiConfig implements MaxApiConfigInterface
{
    /**
     * @var list<positive-int> Time before retry in milliseconds.
     */
    public array $retryAttempts = [1000, 2000, 4000, 8000, 15000];
    /**
     * @var SensitiveParameterValue<non-empty-string>|null
     */
    private ?SensitiveParameterValue $accessToken = null;

    /**
     * @param non-empty-string|null $accessToken
     * @param non-empty-string $baseUrl
     */
    public function __construct(
        #[SensitiveParameter]
        ?string $accessToken = null,
        public HttpClientInterface|null $httpClient = null,
        public string $baseUrl = 'https://platform-api.max.ru'
    ) {
        $this->setAccessToken($accessToken);
    }

    public function getAccessToken(): ?SensitiveParameterValue
    {
        return $this->accessToken;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient ??= (new CurlHttpClient())
            ->setConnectTimeout(5000)
            ->setTimeout(1000)
            ->setUserAgent('mj4444-MaxMessenger-Bot');
    }

    public function getMaxHttpClient(): ?MaxHttpClientInterface
    {
        return null;
    }

    public function getRetryAttempts(): array
    {
        return $this->retryAttempts;
    }

    /**
     * @param non-empty-string|null $accessToken API Max access token.
     * @return $this
     */
    public function setAccessToken(#[SensitiveParameter] ?string $accessToken): self
    {
        $this->accessToken = $accessToken !== null ? new SensitiveParameterValue($accessToken) : null;

        return $this;
    }

    /**
     * @param non-empty-string $baseUrl Base URL of API Max (example: `https://platform-api.max.ru`).
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param HttpClientInterface|null $httpClient HTTP client for API requests.
     * @return $this
     */
    public function setHttpClient(?HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param list<positive-int> $retryAttempts Time before retry in milliseconds.
     * @return $this
     */
    public function setRetryAttempts(array $retryAttempts): self
    {
        $this->retryAttempts = $retryAttempts;

        return $this;
    }
}
