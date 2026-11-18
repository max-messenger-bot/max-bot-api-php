<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;

/**
 * HTTP-запрос.
 *
 * Готовое описание запроса, которое {@see MaxHttpClientInterface} передаёт клиенту.
 * Ограничения времени транспорт применяет, если умеет; {@see PsrHttpClient} их игнорирует.
 */
interface HttpRequestInterface
{
    /**
     * @return BodyInterface|null Тело запроса или `null`, если тела нет.
     *     Для запросов без тела клиент сам решает, нужно ли отправлять пустое тело, исходя из метода запроса.
     */
    public function getBody(): ?BodyInterface;

    /**
     * @return array<non-empty-string, non-empty-string> Заголовки запроса: имя заголовка и его значение.
     */
    public function getHeaders(): array;

    /**
     * @return positive-int|null Наименьшая допустимая скорость передачи в байтах в секунду. Задаётся вместе
     *     с {@see getLowSpeedTime()}: если хотя бы одно из двух значений `null`, скорость не проверяется.
     */
    public function getLowSpeedLimit(): ?int;

    /**
     * @return positive-int|null Сколько секунд скорость может держаться ниже предела,
     *     прежде чем передача будет прервана.
     */
    public function getLowSpeedTime(): ?int;

    /**
     * @return non-empty-string Метод запроса в верхнем регистре, например `GET` или `POST`.
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
