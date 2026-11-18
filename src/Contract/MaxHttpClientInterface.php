<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract;

use Closure;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Интерфейс, реализующий запросы к API Max.
 *
 * Его можно реализовать самостоятельно.
 */
interface MaxHttpClientInterface
{
    /**
     * Выполняет запрос `DELETE`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function delete(string $path, ?array $query = null): array;

    /**
     * Выполняет запрос `GET`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function get(string $path, ?array $query = null): array;

    /**
     * @return HttpClientInterface Клиент, выполняющий запросы.
     */
    public function getHttpClient(): HttpClientInterface;

    /**
     * @return HttpClientInterface Клиент для длинного опроса событий.
     */
    public function getHttpClientForUpdates(): HttpClientInterface;

    /**
     * @return HttpClientInterface Клиент для загрузки медиафайлов.
     */
    public function getHttpClientForUploader(): HttpClientInterface;

    /**
     * Выполняет запрос `GET` за обновлениями через клиент для длинного опроса событий.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @param positive-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     * @return array Тело ответа.
     */
    public function getUpdates(string $path, ?array $query = null, ?int $timeout = null): array;

    /**
     * Выполняет запрос `PATCH`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function patch(string $path, object $body, ?array $query = null): array;

    /**
     * Выполняет запрос `POST`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object|null $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function post(string $path, ?object $body, ?array $query = null): array;

    /**
     * Выполняет запрос `PUT`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function put(string $path, object $body, ?array $query = null): array;

    /**
     * @param Closure(non-empty-string $method, TemporaryExceptionInterface $exception): void|null $exceptionLogger
     *     Обработчик временных ошибок перед повторной попыткой запроса или `null` для отключения.
     * @return $this
     */
    public function setExceptionLogger(?Closure $exceptionLogger): static;

    /**
     * @param non-empty-string|null $version Версия API для параметра строки запроса `v` или `null`,
     *     чтобы не передавать его.
     * @return $this
     */
    public function setVersion(?string $version): static;
}
