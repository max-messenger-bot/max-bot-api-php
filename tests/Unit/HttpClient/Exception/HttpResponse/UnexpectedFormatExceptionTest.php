<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\HttpResponseException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\ParseDataException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;

final class UnexpectedFormatExceptionTest extends Unit
{
    public function testHierarchy(): void
    {
        $exception = new UnexpectedFormatException($this->createRequest(), $this->createResponse(200));

        self::assertInstanceOf(ParseDataException::class, $exception);
        self::assertInstanceOf(HttpResponseException::class, $exception);
        self::assertNotInstanceOf(TemporaryExceptionInterface::class, $exception);
    }

    public function testKeepsRequestResponseAndCode(): void
    {
        $request = $this->createRequest();
        $response = $this->createResponse(200);

        $exception = new UnexpectedFormatException($request, $response);

        self::assertSame('Unexpected Response Format.', $exception->getMessage());
        self::assertSame(200, $exception->getCode());
        self::assertSame($request, $exception->getRequest());
        self::assertSame($response, $exception->getResponse());
        self::assertNull($exception->getPrevious());
    }

    private function createRequest(): HttpRequest
    {
        return new HttpRequest('GET', 'https://example.test/me');
    }

    private function createResponse(int $httpCode): HttpResponse
    {
        return new HttpResponse($httpCode, 'application/json', '[1, 2, 3]');
    }
}
