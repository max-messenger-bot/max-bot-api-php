<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

use MaxMessenger\Bot\Contract\MaxHttpClientInterface;

/**
 * HTTP-ответ.
 *
 * Результат запроса в том виде, в каком его вернул клиент. Логики не содержит: код ответа проверяет
 * и тело разбирает {@see MaxHttpClientInterface}.
 */
interface HttpResponseInterface
{
    /**
     * @return string Тело ответа.
     */
    public function getBody(): string;

    /**
     * @return string|null Значение заголовка `Content-Type` ответа.
     */
    public function getContentType(): ?string;

    /**
     * Первое значение заголовка ответа.
     *
     * @param string $name Имя заголовка без учёта регистра.
     * @return string|null Значение заголовка или `null`, если заголовка нет.
     */
    public function getFirstHeader(string $name): ?string;

    /**
     * Заголовки ответа в формате PSR-7: имя заголовка в том виде, как его прислал сервер,
     * и список его значений. Для поиска без учёта регистра используйте {@see getFirstHeader()}.
     *
     * @return string[][] Заголовки ответа.
     */
    public function getHeaders(): array;

    /**
     * @return int Код ответа HTTP.
     */
    public function getHttpCode(): int;

    /**
     * @return non-empty-string Адрес запроса, на который получен ответ.
     */
    public function getUrl(): string;
}
