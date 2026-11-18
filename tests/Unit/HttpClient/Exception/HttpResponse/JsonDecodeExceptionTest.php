<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\ParseDataException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;

final class JsonDecodeExceptionTest extends Unit
{
    public function testConstructorSetsMessage(): void
    {
        $exception = new JsonDecodeException($this->createRequest(), $this->createResponse(200));

        self::assertSame('JSON decode error.', $exception->getMessage());
    }

    public function testConstructorSetsRequest(): void
    {
        $request = $this->createRequest();

        $exception = new JsonDecodeException($request, $this->createResponse(200));

        self::assertSame($request, $exception->getRequest());
    }

    public function testConstructorSetsResponse(): void
    {
        $response = $this->createResponse(200);

        $exception = new JsonDecodeException($this->createRequest(), $response);

        self::assertSame($response, $exception->getResponse());
    }

    public function testConstructorUsesResponseHttpCode(): void
    {
        $exception = new JsonDecodeException($this->createRequest(), $this->createResponse(400));

        self::assertSame(400, $exception->getCode());
    }

    public function testConstructorWithPrevious(): void
    {
        $previous = new JsonException('Syntax error', code: JSON_ERROR_SYNTAX);

        $exception = new JsonDecodeException($this->createRequest(), $this->createResponse(200), $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testExtendsParseDataException(): void
    {
        $exception = new JsonDecodeException($this->createRequest(), $this->createResponse(200));

        self::assertInstanceOf(ParseDataException::class, $exception);
    }

    private function createRequest(): HttpRequest
    {
        return new HttpRequest('GET', 'https://example.test/messages');
    }

    private function createResponse(int $httpCode): HttpResponse
    {
        return new HttpResponse($httpCode, 'application/json', 'not json');
    }
}
