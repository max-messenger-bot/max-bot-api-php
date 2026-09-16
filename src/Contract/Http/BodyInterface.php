<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract\Http;

/**
 * Тело HTTP-запроса.
 */
interface BodyInterface
{
    /**
     * @return string Содержимое тела запроса.
     */
    public function getBody(): string;

    /**
     * @return non-empty-string|null Значение заголовка `Content-Type`. `null`, если заголовок не нужен.
     */
    public function getContentType(): ?string;
}
