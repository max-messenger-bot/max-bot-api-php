<?php

declare(strict_types=1);

namespace MaxMessenger\Api;

use MaxMessenger\Api\Contracts\MaxBotConfigInterface;
use MaxMessenger\Api\Contracts\PostRequestInterface;
use MaxMessenger\Api\Contracts\RequestInterface;
use MaxMessenger\Api\Exceptions\AccessTokenException;
use Mj4444\SimpleHttpClient\JsonHttpClient;

final readonly class MaxBotRawClient
{
    public function __construct(
        public MaxBotConfigInterface $config
    ) {
    }

    public function delete(string $path, ?RequestInterface $request = null): array
    {
        $baseUrl = $this->config->getBaseUrl();
        $query = $request?->getRequestQuery();
        $query['access_token'] = $this->getAccessToken();

        return $this->getJsonHttpClient()->delete($baseUrl . $path, $query);
    }

    public function get(string $path, ?RequestInterface $request = null): array
    {
        $baseUrl = $this->config->getBaseUrl();
        $query = $request?->getRequestQuery();
        $query['access_token'] = $this->getAccessToken();

        return $this->getJsonHttpClient()->get($baseUrl . $path, $query);
    }

    public function getJsonHttpClient(): JsonHttpClient
    {
        return new JsonHttpClient($this->config->getHttpClient());
    }

    public function patch(string $path, PostRequestInterface $request): array
    {
        $baseUrl = $this->config->getBaseUrl();
        $query = $request instanceof RequestInterface ? $request->getRequestQuery() : [];
        $query['access_token'] = $this->getAccessToken();

        return $this->getJsonHttpClient()->patch($baseUrl . $path, $request, $query);
    }

    /**
     * @return non-falsy-string
     */
    private function getAccessToken(): string
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return $this->config->getAccessToken()
            ?: throw new AccessTokenException();
    }
}
