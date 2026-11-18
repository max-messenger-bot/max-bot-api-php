<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\HttpResponseException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\ParseDataException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedContentTypeException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;

final class UnexpectedContentTypeExceptionTest extends Unit
{
    public function testHierarchy(): void
    {
        $exception = new UnexpectedContentTypeException($this->createRequest(), $this->createResponse(200));

        self::assertInstanceOf(HttpResponseException::class, $exception);
        self::assertInstanceOf(MaxApiException::class, $exception);
        self::assertNotInstanceOf(ParseDataException::class, $exception);
        self::assertNotInstanceOf(TemporaryExceptionInterface::class, $exception);
    }

    public function testKeepsRequestResponseAndCode(): void
    {
        $request = $this->createRequest();
        $response = $this->createResponse(502);

        $exception = new UnexpectedContentTypeException($request, $response);

        self::assertSame('Unexpected ContentType.', $exception->getMessage());
        self::assertSame(502, $exception->getCode());
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
        return new HttpResponse($httpCode, 'text/html', '<html></html>');
    }
}
