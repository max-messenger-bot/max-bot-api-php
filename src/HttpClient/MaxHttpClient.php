<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use Closure;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\Exception\AccessTokenException;
use MaxMessenger\Bot\Exception\HttpClient\HttpResponse\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\InternalHttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ServiceUnavailableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\TooManyRequestsException;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientErrorException;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\InternalServerErrorException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\ServiceUnavailableException as ServiceUnavailableException2;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\TooManyRequestsException as TooManyRequestsException2;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;

use function array_shift;
use function is_array;
use function usleep;

/**
 * HTTP-клиент для API Max.
 */
final class MaxHttpClient implements MaxHttpClientInterface
{
    private ?HttpClientInterface $httpClient = null;

    /**
     * @param Closure(non-empty-string $method, HttpClientException $exception): void|null $exceptionLogger
     */
    public function __construct(
        private readonly MaxApiConfigInterface $config,
        public readonly ?string $version = null,
        private readonly ?Closure $exceptionLogger = null,
    ) {}

    public function delete(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Delete));
    }

    public function get(string $path, ?array $query = null, int $timeout = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Get, null, $timeout));
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient ??= $this->config->getHttpClient();
    }

    public function patch(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Patch, $body));
    }

    public function post(string $path, ?object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Post, $body));
    }

    public function put(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, HttpMethod::Put, $body));
    }

    public function withVersion(?string $version): static
    {
        return new self($this->config, $version, $this->exceptionLogger);
    }

    private function doRequest(JsonRequest $request): array
    {
        $retryAttempts = $this->config->getRetryAttempts();
        do {
            try {
                $response = $this->getHttpClient()->request($request);

                $response->checkHttpCode();

                $responseData = $response->getData();

                if (!is_array($responseData) || empty($responseData)) {
                    /** @psalm-var HttpResponseInterface $response Psalm bug */
                    throw new UnexpectedFormatException($response);
                }

                return $responseData;
            } catch (
                HttpClientErrorException
                |TooManyRequestsException
                |TooManyRequestsException2
                |InternalHttpException
                |InternalServerErrorException
                |ServiceUnavailableException
                |ServiceUnavailableException2 $e
            ) {
                if (!$retryAttempts) {
                    throw $e;
                }
                if ($this->exceptionLogger !== null) {
                    ($this->exceptionLogger)(__METHOD__, $e);
                }
                usleep(array_shift($retryAttempts) * 1000);
            }
        } while (true);
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
        int $timeout = null,
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
