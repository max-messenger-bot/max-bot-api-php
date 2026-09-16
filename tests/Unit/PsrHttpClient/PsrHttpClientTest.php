<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\PsrHttpClient;

use Codeception\Test\Unit;
use LogicException;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\Exception\NetworkException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\PsrHttpClient\Exception\PsrClientException;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrClient;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrClientException;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrNetworkException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class PsrHttpClientTest extends Unit
{
    public function testRequestBuildsPsrRequest(): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $client->request(new HttpRequest(
            'POST',
            'https://example.test/messages?chat_id=1',
            ['Authorization' => 'token', 'Accept' => 'application/json'],
            new StringBody('{"text":"привет"}', 'application/json; charset=utf-8'),
        ));

        $psrRequest = $psrClient->lastRequest;
        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('POST', $psrRequest->getMethod());
        self::assertSame('https://example.test/messages?chat_id=1', (string) $psrRequest->getUri());
        self::assertSame('token', $psrRequest->getHeaderLine('Authorization'));
        self::assertSame('application/json', $psrRequest->getHeaderLine('Accept'));
        self::assertSame('application/json; charset=utf-8', $psrRequest->getHeaderLine('Content-Type'));
        self::assertSame('{"text":"привет"}', (string) $psrRequest->getBody());
    }

    public function testRequestReturnsResponse(): void
    {
        $psrResponse = new Response(
            404,
            ['Content-Type' => 'application/json', 'X-Request-Id' => ['abc']],
            '{"code":"chat.not.found"}',
        );
        $client = $this->createClient(new FakePsrClient($psrResponse));

        $response = $client->request(new HttpRequest('GET', 'https://example.test/chats'));

        self::assertSame(404, $response->getHttpCode());
        self::assertSame('https://example.test/chats', $response->getUrl());
        self::assertSame('application/json', $response->getContentType());
        self::assertSame('{"code":"chat.not.found"}', $response->getBody());
        self::assertSame(['abc'], $response->getHeaders()['X-Request-Id'] ?? null);
        self::assertSame('abc', $response->getFirstHeader('x-request-id'));
    }

    public function testRequestWithoutBodySendsEmptyBody(): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $client->request(new HttpRequest('GET', 'https://example.test/me'));

        $psrRequest = $psrClient->lastRequest;
        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('', (string) $psrRequest->getBody());
        self::assertFalse($psrRequest->hasHeader('Content-Type'));
    }

    public function testRequestWrapsClientException(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $previous = new FakePsrClientException('Client is misconfigured');
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась ошибка клиента');
        } catch (PsrClientException $e) {
            self::assertSame('Client is misconfigured', $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testRequestWrapsNetworkException(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $factory = new Psr17Factory();
        $previous = new FakePsrNetworkException(
            'Connection refused',
            $factory->createRequest('GET', 'https://example.test/me'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась ошибка сети');
        } catch (NetworkException $e) {
            self::assertSame('Connection refused', $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testThrowsWithoutRequestFactory(): void
    {
        $this->expectException(LogicException::class);

        new PsrHttpClient($this->createMock(ClientInterface::class));
    }

    private function createClient(FakePsrClient $psrClient): PsrHttpClient
    {
        $factory = new Psr17Factory();

        return new PsrHttpClient($psrClient, $factory, $factory);
    }
}
