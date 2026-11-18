<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpRequest;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\HttpRequestException;
use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\Tests\Support\Fake\FakeStringCodeException;

final class JsonEncodeExceptionTest extends Unit
{
    public function testCastsNonIntegerCodeOfPrevious(): void
    {
        $previous = new FakeStringCodeException('SQLSTATE[08006] Connection failure', '08006');

        $exception = new JsonEncodeException((object) [], $previous);

        self::assertSame(8006, $exception->getCode());
        self::assertSame('SQLSTATE[08006] Connection failure', $exception->getMessage());
    }

    public function testConstructorSetsData(): void
    {
        $data = ['key' => 'value'];

        $exception = new JsonEncodeException((object) $data, $this->createPrevious());

        self::assertSame($data, (array) $exception->data);
        self::assertSame($data, (array) $exception->getData());
    }

    public function testExtendsHttpRequestException(): void
    {
        $exception = new JsonEncodeException((object) [], $this->createPrevious());

        self::assertInstanceOf(HttpRequestException::class, $exception);
    }

    public function testInheritsCodeFromPrevious(): void
    {
        $previous = new JsonException('Error', 123);

        $exception = new JsonEncodeException((object) [], $previous);

        self::assertSame(123, $exception->getCode());
    }

    public function testPreviousIsSet(): void
    {
        $previous = new JsonException('Previous error');

        $exception = new JsonEncodeException((object) [], $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    private function createPrevious(): JsonException
    {
        return new JsonException('Encode error');
    }
}
