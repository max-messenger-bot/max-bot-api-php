<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\HttpClient\Exceptions\AccessTokenException;
use MaxMessenger\Bot\HttpClient\Exceptions\UnexpectedFormatException;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;

use function is_array;

/**
 * HTTP-клиент для API Max.
 */
final readonly class MaxHttpClient
{
    public function __construct(
        private MaxApiConfigInterface $config,
        public ?string $version = null,
    ) {
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function delete(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Delete));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @param positive-int|null $timeout
     */
    public function get(string $path, ?array $query = null, int $timeout = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Get, null, $timeout));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function patch(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Patch, $body));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function post(string $path, ?object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Post, $body));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function put(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Put, $body));
    }

    public function withVersion(?string $version): self
    {
        return new self($this->config, $version);
    }

    private function doRequest(JsonRequest $request): array
    {
        $response = $this->config->getHttpClient()->request($request);

        $response->checkHttpCode();

        $responseData = $response->getData();

        if (!is_array($responseData) || empty($responseData)) {
            /** @psalm-var HttpResponseInterface $response Psalm bug */
            throw new UnexpectedFormatException($response);
        }

        return $responseData;
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @param positive-int|null $timeout
     */
    private function makeRequest(
        string $path,
        ?array $query,
        HttpMethod $method,
        ?object $body = null,
        int $timeout = null
    ): JsonRequest {
        $url = $this->config->getBaseUrl() . $path;
        $accessToken = $this->config->getAccessToken() ?? throw new AccessTokenException();

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($this->version) {
            if ($query !== null) {
                $query['v'] ??= $this->version;
            } else {
                $query = ['v' => $this->version];
            }
        }

        return new JsonRequest($url, $query, $method, $body, $timeout, $accessToken);
    }
}
