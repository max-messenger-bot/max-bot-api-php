<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;

/**
 * Фейковый HTTP-клиент для герметичных тестов: запоминает выполненные запросы
 * и возвращает заранее заданный ответ вместо обращения к сети.
 */
final class FakeMaxHttpClient implements MaxHttpClientInterface
{
    /**
     * @var list<array{method: string, path: string, body: object|null, query: array<string|int>|null}>
     */
    public array $calls = [];

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(public array $response = [])
    {
        if ($this->response === []) {
            $this->response = [
                'success' => true,
                'message' => self::messageData(),
            ];
        }
    }

    public function delete(string $path, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'delete', 'path' => $path, 'body' => null, 'query' => $query];

        return $this->response;
    }

    public function get(string $path, ?array $query = null, ?int $timeout = null): array
    {
        $this->calls[] = ['method' => 'get', 'path' => $path, 'body' => null, 'query' => $query];

        return $this->response;
    }

    public function getHttpClient(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    /**
     * @return array{method: string, path: string, body: object|null, query: array<string|int>|null}|null
     */
    public function lastCall(): ?array
    {
        return $this->calls[array_key_last($this->calls)] ?? null;
    }

    /**
     * Готовые данные сообщения для ответов API.
     *
     * @return array<string, mixed>
     */
    public static function messageData(): array
    {
        return [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.out', 'seq' => 1, 'text' => 'ok'],
        ];
    }

    public function patch(string $path, object $body, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'patch', 'path' => $path, 'body' => $body, 'query' => $query];

        return $this->response;
    }

    public function post(string $path, ?object $body, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'post', 'path' => $path, 'body' => $body, 'query' => $query];

        return $this->response;
    }

    public function put(string $path, object $body, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'put', 'path' => $path, 'body' => $body, 'query' => $query];

        return $this->response;
    }

    public function withVersion(?string $version): static
    {
        return $this;
    }
}
