<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exceptions\HttpClient\HttpRequest;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exceptions\HttpClient\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\Exceptions\MaxApiException;

final class JsonEncodeExceptionTest extends Unit
{
    public function testConstructorSetsData(): void
    {
        $data = ['key' => 'value'];

        $exception = new JsonEncodeException((object)$data, $this->createPrevious());

        self::assertSame($data, (array)$exception->data);
        self::assertSame($data, (array)$exception->getData());
    }

    public function testExtendsMaxApiException(): void
    {
        $data = ['foo' => 'bar'];

        $exception = new JsonEncodeException((object)$data, $this->createPrevious());

        self::assertInstanceOf(MaxApiException::class, $exception);
    }

    public function testInheritsCodeFromPrevious(): void
    {
        $previous = new JsonException('Error', 123);

        $exception = new JsonEncodeException((object)[], $previous);

        self::assertSame(123, $exception->getCode());
    }

    public function testInheritsMessageFromPrevious(): void
    {
        $previous = new JsonException('Malformed UTF-8', code: JSON_ERROR_UTF8);

        $exception = new JsonEncodeException((object)[], $previous);

        self::assertSame('Malformed UTF-8', $exception->getMessage());
    }

    public function testPreviousIsSet(): void
    {
        $previous = new JsonException('Previous error');

        $exception = new JsonEncodeException((object)[], $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testWithComplexData(): void
    {
        $data = [
            'name' => 'test',
            'items' => [1, 2, 3],
            'nested' => ['key' => 'value'],
        ];

        $exception = new JsonEncodeException((object)$data, $this->createPrevious());

        $result = (array)$exception->getData();
        self::assertSame('test', $result['name']);
        self::assertSame([1, 2, 3], $result['items']);
        self::assertSame(['key' => 'value'], (array)$result['nested']);
    }

    private function createPrevious(): JsonException
    {
        return new JsonException('Encode error');
    }
}
