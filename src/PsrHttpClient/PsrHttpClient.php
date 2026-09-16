<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\PsrHttpClient;

use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\HttpClient\Exception\NetworkException;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\PsrHttpClient\Exception\PsrClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * HTTP-клиент поверх любой реализации PSR-18.
 *
 * Переводит {@see HttpRequestInterface} в запрос PSR-7 и возвращает {@see HttpResponse}. Подходит, когда
 * в проекте уже есть настроенный клиент: Guzzle, Symfony HttpClient и подобные.
 *
 * Таймаут запроса {@see HttpRequestInterface::getTimeout()} не применяется: в PSR-18 таймаутов нет,
 * их задают при настройке самого клиента. Для длинного опроса событий увеличьте таймаут своего клиента.
 */
final class PsrHttpClient implements HttpClientInterface
{
    private readonly RequestFactoryInterface $requestFactory;
    private readonly StreamFactoryInterface $streamFactory;

    /**
     * @param ClientInterface $client Клиент PSR-18, выполняющий запросы.
     * @param RequestFactoryInterface|null $requestFactory Фабрика запросов PSR-17. Если не задана, используется
     *     сам клиент, когда он реализует этот интерфейс.
     * @param StreamFactoryInterface|null $streamFactory Фабрика потоков PSR-17. Если не задана, используется
     *     сам клиент, когда он реализует этот интерфейс.
     */
    public function __construct(
        private readonly ClientInterface $client,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
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

    public function request(HttpRequestInterface $request): HttpResponseInterface
    {
        $psrRequest = $this->requestFactory->createRequest($request->getMethod(), $request->getUrl());

        foreach ($request->getHeaders() as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        $body = $request->getBody();
        if ($body !== null) {
            $contentType = $body->getContentType();
            if ($contentType !== null) {
                $psrRequest = $psrRequest->withHeader('Content-Type', $contentType);
            }

            $psrRequest = $psrRequest->withBody($this->streamFactory->createStream($body->getBody()));
        }

        try {
            $psrResponse = $this->client->sendRequest($psrRequest);
        } catch (NetworkExceptionInterface $e) {
            throw new NetworkException($e->getMessage(), $e->getCode(), $request, $e);
        } catch (ClientExceptionInterface $e) {
            throw new PsrClientException($e->getMessage(), $e->getCode(), $request, $e);
        }

        return new HttpResponse(
            $psrResponse->getStatusCode(),
            $request->getUrl(),
            $psrResponse->getHeaderLine('Content-Type') ?: null,
            (string) $psrResponse->getBody(),
            $psrResponse->getHeaders(),
        );
    }
}
