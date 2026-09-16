<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use MaxMessenger\Bot\Contract\MaxHttpClientInterface;

/**
 * HTTP-запрос.
 *
 * Готовое описание запроса, которое {@see MaxHttpClientInterface} передаёт клиенту.
 * Логики не содержит: адрес, заголовки и тело формирует вызывающая сторона.
 */
interface HttpRequestInterface
{
    /**
     * Тело запроса.
     *
     * Клиент передаёт тело серверу, если оно задано. Для запросов без тела возвращает `null`:
     * клиент сам решает, нужно ли отправлять пустое тело, исходя из метода запроса.
     *
     * @return BodyInterface|null Тело запроса или `null`, если тела нет.
     */
    public function getBody(): ?BodyInterface;

    /**
     * @return array<non-empty-string, non-empty-string> Заголовки запроса: имя заголовка и его значение.
     */
    public function getHeaders(): array;

    /**
     * @return non-empty-string Метод запроса, например `GET` или `POST`.
     */
    public function getMethod(): string;

    /**
     * @return non-negative-int|null Максимальное время выполнения запроса в миллисекундах.
     *     `0` — ждать неограниченно, `null` — использовать значение клиента.
     */
    public function getTimeout(): ?int;

    /**
     * @return non-empty-string Адрес запроса вместе со строкой параметров.
     */
    public function getUrl(): string;
}
