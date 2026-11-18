<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpRequest;

use Throwable;

/**
 * Ошибка кодирования в JSON.
 *
 * Тело запроса закодировать не удалось, поэтому ошибка хранит не запрос, а само тело.
 */
final class JsonEncodeException extends HttpRequestException
{
    /**
     * @param array|object $data Данные, которые не удалось закодировать.
     * @param Throwable $previous Исходная ошибка.
     */
    public function __construct(
        public readonly array|object $data,
        Throwable $previous,
    ) {
        parent::__construct($previous->getMessage(), (int) $previous->getCode(), $previous);
    }

    /**
     * @return array|object Данные, которые не удалось закодировать.
     */
    public function getData(): array|object
    {
        return $this->data;
    }
}
