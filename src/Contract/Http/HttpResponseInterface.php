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
     * @param string $name Имя заголовка без учёта регистра.
     * @return string|null Первое значение заголовка ответа или `null`, если заголовка нет.
     */
    public function getFirstHeader(string $name): ?string;

    /**
     * @return string[][] Заголовки ответа в формате PSR-7: имя заголовка в том виде, как его прислал сервер,
     *     и список его значений. Для поиска без учёта регистра используйте {@see getFirstHeader()}.
     */
    public function getHeaders(): array;

    /**
     * @return int Код ответа HTTP.
     */
    public function getHttpCode(): int;
}
