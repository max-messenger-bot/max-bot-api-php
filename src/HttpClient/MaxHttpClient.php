<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\HttpClient\Exceptions\AccessTokenException;
use MaxMessenger\Bot\HttpClient\Exceptions\UnexpectedFormatException;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

use function is_array;
use function is_string;

final readonly class MaxHttpClient
{
    public function __construct(
        private MaxApiConfigInterface $config
    ) {
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function delete(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Delete));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function get(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Get));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function patch(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Patch, $body));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function post(string $path, ?object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Post, $body));
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function put(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Put, $body));
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
     * @return SensitiveParameterValue<non-empty-string>
     */
    private function getAccessToken(): SensitiveParameterValue
    {
        $accessToken = $this->config->getAccessToken();
        $_value = $accessToken?->getValue();

        /** @var SensitiveParameterValue<non-empty-string> */
        return is_string($_value) && !empty($_value)
            ? $accessToken
            : throw new AccessTokenException();
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    private function makeRequest(string $path, ?array $query, HttpMethod $method, ?object $body = null): JsonRequest
    {
        $url = $this->config->getBaseUrl() . $path;

        return new JsonRequest($url, $query, $method, $body, $this->getAccessToken());
    }
}
