<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract;

use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;

/**
 * An interface that implements requests to the MAX API.
 *
 * You can implement it yourself.
 */
interface MaxHttpClientInterface
{
    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function delete(string $path, ?array $query = null): array;

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     * @param positive-int|null $timeout
     */
    public function get(string $path, ?array $query = null, int $timeout = null): array;

    public function getHttpClient(): HttpClientInterface;

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function patch(string $path, object $body, ?array $query = null): array;

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function post(string $path, ?object $body, ?array $query = null): array;

    /**
     * @param non-empty-string $path
     * @param array<string|int>|null $query
     */
    public function put(string $path, object $body, ?array $query = null): array;

    public function withVersion(?string $version): static;
}
