<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Exception\MaxApiException;
use Throwable;

/**
 * Базовый класс ошибок HTTP-клиента: ответ от сервера не получен.
 *
 * От него наследуются ошибки транспорта: {@see TemporaryNetworkException}, а также собственные ошибки клиентов.
 */
abstract class HttpClientException extends MaxApiException
{
    /**
     * @param string $message Описание ошибки.
     * @param int $code Код ошибки транспорта.
     * @param HttpRequestInterface $request Запрос, который не удалось выполнить.
     * @param Throwable|null $previous Исходная ошибка транспорта.
     */
    public function __construct(
        string $message,
        int $code,
        public readonly HttpRequestInterface $request,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return HttpRequestInterface Запрос, который не удалось выполнить.
     */
    public function getRequest(): HttpRequestInterface
    {
        return $this->request;
    }
}
