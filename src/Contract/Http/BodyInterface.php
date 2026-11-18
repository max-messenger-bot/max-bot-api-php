<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

/**
 * Тело HTTP-запроса.
 */
interface BodyInterface
{
    /**
     * @return StreamInterface|string Содержимое тела запроса: строка или поток,
     *     из которого клиент читает данные порциями.
     */
    public function getBody(): StreamInterface|string;

    /**
     * @return non-empty-string Значение заголовка `Content-Type`.
     */
    public function getContentType(): string;
}
