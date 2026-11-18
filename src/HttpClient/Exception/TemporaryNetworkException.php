<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception;

use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;
use Throwable;

/**
 * Временная ошибка сети: ответ от сервера не получен.
 *
 * Соединение не установлено или оборвалось. Истёкшее время ожидания выделено в {@see TimeoutException}.
 *
 * Описание и код берутся из исходной ошибки, она же остаётся в {@see Throwable::getPrevious()}:
 * у {@see CurlHttpClient} это {@see CurlException} с кодом curl, у {@see PsrHttpClient} — исключение клиента PSR-18.
 * Стандарт PSR-18 кодов не описывает: у большинства реализаций там `0`, а у остальных значение своё,
 * поэтому сравнивать код с кодами curl можно, только зная транспорт.
 */
class TemporaryNetworkException extends HttpClientException implements TemporaryExceptionInterface
{
    /**
     * @param Throwable $previous Исходная ошибка транспорта.
     * @param HttpRequestInterface $request Запрос, который не удалось выполнить.
     */
    public function __construct(Throwable $previous, HttpRequestInterface $request)
    {
        parent::__construct($previous->getMessage(), (int) $previous->getCode(), $request, $previous);
    }
}
