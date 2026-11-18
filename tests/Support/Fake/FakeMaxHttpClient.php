<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use Closure;
use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use Throwable;

use function array_key_last;
use function array_shift;

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
    public ?Closure $exceptionLogger = null;
    /**
     * @var list<Throwable> Исключения вместо ответа: по одному на очередной запрос.
     */
    public array $exceptions = [];
    /**
     * @var int|null Таймаут последнего вызова {@see getUpdates()} в миллисекундах.
     */
    public ?int $updatesTimeout = null;

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

    /**
     * Готовые данные комментария к посту в канале для ответов API.
     *
     * @return array<string, mixed>
     */
    public static function commentData(): array
    {
        return [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
        ];
    }

    public function delete(string $path, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'delete', 'path' => $path, 'body' => null, 'query' => $query];

        $this->throwIfQueued();

        return $this->response;
    }

    public function get(string $path, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'get', 'path' => $path, 'body' => null, 'query' => $query];

        $this->throwIfQueued();

        return $this->response;
    }

    public function getHttpClient(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getHttpClientForUpdates(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getHttpClientForUploader(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getUpdates(string $path, ?array $query = null, ?int $timeout = null): array
    {
        $this->calls[] = ['method' => 'getUpdates', 'path' => $path, 'body' => null, 'query' => $query];
        $this->updatesTimeout = $timeout;

        $this->throwIfQueued();

        return $this->response;
    }

    /**
     * @return array{method: string, path: string, body: object|null, query: array<string|int>|null}|null
     */
    public function lastCall(): ?array
    {
        $key = array_key_last($this->calls);

        return $key === null ? null : $this->calls[$key];
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

        $this->throwIfQueued();

        return $this->response;
    }

    public function post(string $path, ?object $body, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'post', 'path' => $path, 'body' => $body, 'query' => $query];

        $this->throwIfQueued();

        return $this->response;
    }

    public function put(string $path, object $body, ?array $query = null): array
    {
        $this->calls[] = ['method' => 'put', 'path' => $path, 'body' => $body, 'query' => $query];

        $this->throwIfQueued();

        return $this->response;
    }

    public function setExceptionLogger(?Closure $exceptionLogger): static
    {
        $this->exceptionLogger = $exceptionLogger;

        return $this;
    }

    public function setVersion(?string $version): static
    {
        return $this;
    }

    /**
     * Выбрасывает очередное заданное исключение, если оно есть.
     *
     * @throws Throwable Исключение из начала очереди {@see $exceptions}.
     */
    private function throwIfQueued(): void
    {
        $exception = array_shift($this->exceptions);

        if ($exception !== null) {
            throw $exception;
        }
    }
}
