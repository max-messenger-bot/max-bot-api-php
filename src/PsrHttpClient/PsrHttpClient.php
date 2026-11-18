<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\PsrHttpClient;

use Closure;
use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\Contract\Http\StreamInterface;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\HttpClient\TransportErrorType;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

use function in_array;
use function preg_match;

/**
 * HTTP-клиент поверх любой реализации PSR-18.
 *
 * Переводит {@see HttpRequestInterface} в запрос PSR-7 и возвращает {@see HttpResponse}.
 * Подходит, когда в проекте уже есть настроенный клиент: Guzzle, Symfony HttpClient и подобные.
 *
 * Таймаут запроса {@see HttpRequestInterface::getTimeout()} и ограничения скорости передачи игнорируются:
 * их задают при настройке используемого клиента PSR-18. Для длинного опроса событий увеличьте таймаут клиента.
 */
final readonly class PsrHttpClient implements HttpClientInterface
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /**
     * @param ClientInterface $client Клиент PSR-18, выполняющий запросы.
     * @param RequestFactoryInterface|null $requestFactory Фабрика запросов PSR-17.
     *     Если не задана, используется сам клиент, когда он реализует этот интерфейс.
     * @param StreamFactoryInterface|null $streamFactory Фабрика потоков PSR-17.
     *     Если не задана, используется сам клиент, когда он реализует этот интерфейс.
     * @param (Closure(ClientExceptionInterface): ?TransportErrorType)|null $errorClassifier Определитель ошибок
     *     клиента. `null` вызывает встроенное правило; {@see TransportErrorType::Permanent} отключает повтор.
     */
    public function __construct(
        private ClientInterface $client,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        private ?Closure $errorClassifier = null,
    ) {
        $this->requestFactory = $requestFactory
            ?? ($client instanceof RequestFactoryInterface
                ? $client
                : throw new LogicException('PSR-17 request factory is required.'));

        $this->streamFactory = $streamFactory
            ?? ($client instanceof StreamFactoryInterface
                ? $client
                : throw new LogicException('PSR-17 stream factory is required.'));
    }

    /**
     * Выполняет запрос через клиент PSR-18.
     *
     * @param HttpRequestInterface $request Объект запроса.
     * @return HttpResponseInterface Ответ сервера.
     * @throws TimeoutException Если истекло время ожидания.
     * @throws TemporaryNetworkException Если соединение или передача временно не удались.
     * @throws ClientExceptionInterface Прочие ошибки клиента PSR-18 пробрасываются как есть.
     */
    public function request(HttpRequestInterface $request): HttpResponseInterface
    {
        $psrRequest = $this->requestFactory->createRequest($request->getMethod(), $request->getUrl());

        foreach ($request->getHeaders() as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        $body = $request->getBody();
        if ($body !== null) {
            $psrRequest = $psrRequest->withHeader('Content-Type', $body->getContentType());

            $content = $body->getBody();
            if ($content instanceof StreamInterface) {
                $content->seek(0);
            }

            $psrRequest = $psrRequest->withBody(
                $content instanceof StreamInterface
                    ? new PsrStream($content)
                    : $this->streamFactory->createStream($content),
            );
        }

        try {
            $psrResponse = $this->client->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $e) {
            $type = $this->errorClassifier?->__invoke($e) ?? self::classifyError($e);

            throw match ($type) {
                TransportErrorType::Timeout => new TimeoutException($e, $request),
                TransportErrorType::TemporaryNetwork => new TemporaryNetworkException($e, $request),
                default => $e,
            };
        }

        return new HttpResponse(
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaderLine('Content-Type') ?: null,
            (string) $psrResponse->getBody(),
            $psrResponse->getHeaders(),
        );
    }

    private static function classifyError(ClientExceptionInterface $exception): ?TransportErrorType
    {
        if (self::isTimeout($exception)) {
            return TransportErrorType::Timeout;
        }

        if ($exception instanceof NetworkExceptionInterface || self::isTemporaryCurlError($exception)) {
            return TransportErrorType::TemporaryNetwork;
        }

        return null;
    }

    /**
     * Распознаёт временную ошибку curl по тексту `cURL error N:` в цепочке исключений.
     *
     * @param Throwable $exception Ошибка клиента PSR-18.
     * @return bool `true`, если код curl относится к временным сбоям соединения или передачи.
     */
    private static function isTemporaryCurlError(Throwable $exception): bool
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if (preg_match('/\bcURL error (\d+):/i', $current->getMessage(), $matches) === 1) {
                // Число берётся только из сообщения curl, не из кода исключения PSR-клиента.
                return in_array((int) $matches[1], [5, 6, 7, 18, 28, 35, 52, 55, 56], true);
            }
        }

        return false;
    }

    /**
     * Распознаёт истёкшее время ожидания по тексту ошибки клиента.
     *
     * PSR-18 не даёт для таймаута ни кода, ни отдельного типа, поэтому остаётся только текст.
     * Клиенты на curl пишут `timed out` или `Timeout was reached` по-английски, Symfony при простое
     * соединения пишет `Idle timeout reached`, а клиенты на потоках PHP сообщают лишь `HTTP request failed!` —
     * там таймаут ответа неотличим от прочих сбоев. Слово `timeout` засчитывается только с пробелом перед
     * ним или после него: клиенты добавляют в текст ошибки адрес запроса, а в адресе `/updates?timeout=30`
     * это слово тоже есть.
     *
     * @param Throwable $exception Ошибка клиента PSR-18.
     * @return bool `true`, если ошибка похожа на истёкшее время ожидания.
     */
    private static function isTimeout(Throwable $exception): bool
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if (preg_match('/\btimed[\s-]?out\b|\stimeout\b|\btimeout\s/i', $current->getMessage()) === 1) {
                return true;
            }
        }

        return false;
    }
}
