<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use JsonSerializable;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\HttpClient\Exceptions\AccessTokenException;
use MaxMessenger\Bot\HttpClient\Exceptions\UnexpectedFormatException;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

use function is_array;

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
        return $this->doGet($path, $query, HttpMethod::Delete);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function get(string $path, ?array $query = null): array
    {
        return $this->doGet($path, $query, HttpMethod::Get);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function patch(string $path, JsonSerializable $body, ?array $query = null): array
    {
        return $this->doPost($path, $body->jsonSerialize(), $query, HttpMethod::Patch);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function post(string $path, JsonSerializable $body, ?array $query = null): array
    {
        return $this->doPost($path, $body->jsonSerialize(), $query, HttpMethod::Post);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    public function put(string $path, JsonSerializable $body, ?array $query = null): array
    {
        return $this->doPost($path, $body->jsonSerialize(), $query, HttpMethod::Put);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    private function doGet(string $path, ?array $query, HttpMethod $method): array
    {
        $request = $this->makeRequest($path, $query, $method);

        return $this->doRequest($request);
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @throws HttpClientException
     * @throws HttpException
     */
    private function doPost(string $path, array $body, ?array $query, HttpMethod $method): array
    {
        $request = $this->makeRequest($path, $query, $method, $body);

        return $this->doRequest($request);
    }

    private function doRequest(JsonRequest $request): array
    {
        $response = $this->config->getHttpClient()->request($request);

        $response->checkHttpCode();

        $responseData = $response->getData();

        if (!is_array($responseData) || empty($responseData)) {
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

        return $accessToken !== null && !empty($accessToken->getValue())
            ? $accessToken
            : throw new AccessTokenException();
    }

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    private function makeRequest(string $path, ?array $query, HttpMethod $method, ?array $body = null): JsonRequest
    {
        $url = $this->config->getBaseUrl() . $path;

        return new JsonRequest($url, $query, $method, $body, $this->getAccessToken());
    }
}
