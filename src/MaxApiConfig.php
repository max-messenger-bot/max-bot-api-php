<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\CurlHttpClient;
use SensitiveParameter;
use SensitiveParameterValue;

final class MaxApiConfig implements MaxApiConfigInterface
{
    /**
     * @var SensitiveParameterValue<string>|null
     */
    private ?SensitiveParameterValue $accessToken = null;

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
        return $this->httpClient ??= new CurlHttpClient();
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken !== null ? new SensitiveParameterValue($accessToken) : null;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function setHttpClient(?HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }
}
