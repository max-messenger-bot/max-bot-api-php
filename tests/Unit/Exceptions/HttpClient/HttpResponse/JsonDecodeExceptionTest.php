<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exceptions\HttpClient\HttpResponse;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse\ParseDataException;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class JsonDecodeExceptionTest extends Unit
{
    public function testConstructorSetsMessage(): void
    {
        $response = $this->createMockResponse(200);

        $exception = new JsonDecodeException($response);

        self::assertSame('JSON decode error.', $exception->getMessage());
    }

    public function testConstructorSetsResponse(): void
    {
        $response = $this->createMockResponse(200);

        $exception = new JsonDecodeException($response);

        self::assertSame($response, $exception->getResponse());
    }

    public function testConstructorUsesResponseHttpCode(): void
    {
        $response = $this->createMockResponse(400);

        $exception = new JsonDecodeException($response);

        self::assertSame(400, $exception->getCode());
    }

    public function testConstructorWithPrevious(): void
    {
        $response = $this->createMockResponse(200);
        $previous = new JsonException('Syntax error', code: JSON_ERROR_SYNTAX);

        $exception = new JsonDecodeException($response, $previous);

        self::assertSame($previous, $exception->getPrevious());
        self::assertSame('Syntax error', $exception->getPrevious()->getMessage());
    }

    public function testExtendsParseDataException(): void
    {
        $response = $this->createMockResponse(200);

        $exception = new JsonDecodeException($response);

        self::assertInstanceOf(ParseDataException::class, $exception);
    }

    public function testWithJsonResponse(): void
    {
        $response = $this->createMockResponse(200);

        $exception = new JsonDecodeException($response);

        self::assertSame($response, $exception->getResponse());
        self::assertSame(200, $exception->getCode());
    }

    /**
     * @psalm-return HttpResponseInterface&MockObject
     */
    private function createMockResponse(int $httpCode): HttpResponseInterface
    {
        $mock = $this->createMock(HttpResponseInterface::class);
        $mock->method('getHttpCode')->willReturn($httpCode);

        return $mock;
    }
}
